#!/usr/bin/env node
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import readline from "node:readline";

function parseDotenvValue(raw) {
  const trimmed = raw.trim();
  if (
    (trimmed.startsWith('"') && trimmed.endsWith('"')) ||
    (trimmed.startsWith("'") && trimmed.endsWith("'"))
  ) {
    return trimmed.slice(1, -1);
  }
  return trimmed;
}

function loadEnvFileIfPresent(filePath) {
  if (!fs.existsSync(filePath)) return;
  const content = fs.readFileSync(filePath, "utf8");
  for (const line of content.split(/\r?\n/)) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith("#")) continue;

    const withoutExport = trimmed.startsWith("export ") ? trimmed.slice("export ".length) : trimmed;
    const eqIdx = withoutExport.indexOf("=");
    if (eqIdx <= 0) continue;

    const key = withoutExport.slice(0, eqIdx).trim();
    const rawValue = withoutExport.slice(eqIdx + 1);
    if (!key) continue;
    if (Object.prototype.hasOwnProperty.call(process.env, key)) continue;

    process.env[key] = parseDotenvValue(rawValue);
  }
}

function loadLocalEnv() {
  const scriptDir = path.dirname(fileURLToPath(import.meta.url));
  const candidates = [
    path.join(process.cwd(), ".env"),
    path.join(process.cwd(), ".env.local"),
    path.resolve(scriptDir, "..", ".env"),
    path.resolve(scriptDir, "..", ".env.local"),
  ];
  for (const candidate of candidates) loadEnvFileIfPresent(candidate);
}

loadLocalEnv();

const DEFAULT_API_BASE_URL = "https://api.render.com/v1";
const apiBaseUrl = (process.env.RENDER_API_BASE_URL || DEFAULT_API_BASE_URL).replace(/\/+$/, "");

function requireApiKey() {
  const apiKey = process.env.RENDER_API_KEY;
  if (!apiKey) {
    throw new Error("Missing env var RENDER_API_KEY");
  }
  return apiKey;
}

function jsonRpcResult(id, result) {
  return { jsonrpc: "2.0", id, result };
}

function jsonRpcError(id, code, message, data) {
  return { jsonrpc: "2.0", id, error: { code, message, data } };
}

function toolText(text) {
  return { content: [{ type: "text", text }] };
}

async function renderFetch(path, { method = "GET", query, jsonBody } = {}) {
  const apiKey = requireApiKey();

  const url = new URL(apiBaseUrl + (path.startsWith("/") ? path : `/${path}`));
  if (query && typeof query === "object") {
    for (const [key, value] of Object.entries(query)) {
      if (value === undefined || value === null) continue;
      url.searchParams.set(key, String(value));
    }
  }

  const res = await fetch(url, {
    method,
    headers: {
      Authorization: `Bearer ${apiKey}`,
      Accept: "application/json",
      ...(jsonBody ? { "Content-Type": "application/json" } : {}),
    },
    body: jsonBody ? JSON.stringify(jsonBody) : undefined,
  });

  const contentType = res.headers.get("content-type") || "";
  const bodyText = await res.text();
  const body = contentType.includes("application/json") && bodyText ? JSON.parse(bodyText) : bodyText;

  if (!res.ok) {
    const message = typeof body === "string" ? body : JSON.stringify(body);
    const err = new Error(`Render API error ${res.status}: ${message}`);
    err.status = res.status;
    err.body = body;
    throw err;
  }

  return body;
}

const TOOLS = [
  {
    name: "render_list_services",
    description: "List Render services (Render API: GET /services).",
    inputSchema: { type: "object", additionalProperties: false, properties: {} },
  },
  {
    name: "render_get_service",
    description: "Get a Render service by id (Render API: GET /services/:serviceId).",
    inputSchema: {
      type: "object",
      additionalProperties: false,
      properties: { serviceId: { type: "string", minLength: 1 } },
      required: ["serviceId"],
    },
  },
  {
    name: "render_create_deploy",
    description: "Create a deploy for a service (Render API: POST /services/:serviceId/deploys).",
    inputSchema: {
      type: "object",
      additionalProperties: false,
      properties: {
        serviceId: { type: "string", minLength: 1 },
        clearCache: { type: "boolean" },
      },
      required: ["serviceId"],
    },
  },
  {
    name: "render_list_deploys",
    description: "List deploys for a service (Render API: GET /services/:serviceId/deploys).",
    inputSchema: {
      type: "object",
      additionalProperties: false,
      properties: {
        serviceId: { type: "string", minLength: 1 },
        limit: { type: "integer", minimum: 1, maximum: 200 },
      },
      required: ["serviceId"],
    },
  },
  {
    name: "render_get_deploy",
    description: "Get a deploy by id (Render API: GET /deploys/:deployId).",
    inputSchema: {
      type: "object",
      additionalProperties: false,
      properties: { deployId: { type: "string", minLength: 1 } },
      required: ["deployId"],
    },
  },
  {
    name: "render_request",
    description:
      "Make an arbitrary Render API request (base is https://api.render.com/v1). Use when you need an endpoint not covered by other tools.",
    inputSchema: {
      type: "object",
      additionalProperties: false,
      properties: {
        method: {
          type: "string",
          enum: ["GET", "POST", "PUT", "PATCH", "DELETE"],
          default: "GET",
        },
        path: { type: "string", minLength: 1, description: "Example: /services or /services/<id>/deploys" },
        query: { type: "object", additionalProperties: { type: ["string", "number", "boolean"] } },
        jsonBody: { type: ["object", "array", "null"] },
      },
      required: ["path"],
    },
  },
];

async function callTool(name, args) {
  switch (name) {
    case "render_list_services": {
      const body = await renderFetch("/services");
      return toolText(JSON.stringify(body, null, 2));
    }
    case "render_get_service": {
      const body = await renderFetch(`/services/${encodeURIComponent(args.serviceId)}`);
      return toolText(JSON.stringify(body, null, 2));
    }
    case "render_create_deploy": {
      const body = await renderFetch(`/services/${encodeURIComponent(args.serviceId)}/deploys`, {
        method: "POST",
        jsonBody: args.clearCache === undefined ? undefined : { clearCache: args.clearCache },
      });
      return toolText(JSON.stringify(body, null, 2));
    }
    case "render_list_deploys": {
      const body = await renderFetch(`/services/${encodeURIComponent(args.serviceId)}/deploys`, {
        query: args.limit ? { limit: args.limit } : undefined,
      });
      return toolText(JSON.stringify(body, null, 2));
    }
    case "render_get_deploy": {
      const body = await renderFetch(`/deploys/${encodeURIComponent(args.deployId)}`);
      return toolText(JSON.stringify(body, null, 2));
    }
    case "render_request": {
      const body = await renderFetch(args.path, {
        method: args.method || "GET",
        query: args.query,
        jsonBody: args.jsonBody,
      });
      return toolText(JSON.stringify(body, null, 2));
    }
    default:
      throw new Error(`Unknown tool: ${name}`);
  }
}

function write(message) {
  process.stdout.write(`${JSON.stringify(message)}\n`);
}

const rl = readline.createInterface({ input: process.stdin, crlfDelay: Infinity });

rl.on("line", async (line) => {
  if (!line.trim()) return;

  let msg;
  try {
    msg = JSON.parse(line);
  } catch (e) {
    process.stderr.write(`Invalid JSON from client: ${e}\n`);
    return;
  }

  const { id, method, params } = msg || {};

  try {
    if (method === "initialize") {
      write(
        jsonRpcResult(id, {
          protocolVersion: params?.protocolVersion ?? "2024-11-05",
          capabilities: { tools: {} },
          serverInfo: { name: "render-mcp-local", version: "0.1.0" },
        }),
      );
      return;
    }

    if (method === "notifications/initialized") return;

    if (method === "ping") {
      write(jsonRpcResult(id, {}));
      return;
    }

    if (method === "tools/list") {
      write(jsonRpcResult(id, { tools: TOOLS }));
      return;
    }

    if (method === "resources/list") {
      write(jsonRpcResult(id, { resources: [] }));
      return;
    }

    if (method === "prompts/list") {
      write(jsonRpcResult(id, { prompts: [] }));
      return;
    }

    if (method === "tools/call") {
      const toolName = params?.name;
      const toolArgs = params?.arguments ?? {};
      const result = await callTool(toolName, toolArgs);
      write(jsonRpcResult(id, result));
      return;
    }

    if (id !== undefined) {
      write(jsonRpcError(id, -32601, `Method not found: ${method}`));
    }
  } catch (e) {
    if (id === undefined) return;
    write(
      jsonRpcError(id, -32000, e?.message || "Server error", {
        ...(e?.status ? { status: e.status } : {}),
        ...(e?.body ? { body: e.body } : {}),
      }),
    );
  }
});

rl.on("close", () => process.exit(0));
