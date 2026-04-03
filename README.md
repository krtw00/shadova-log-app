# Shadova Log App

> Archived on April 3, 2026. The service is scheduled for shutdown because there are no active users. This repository is kept for reference only.

[日本語](README.ja.md) | English

A battle record management web application for Shadowverse: Worlds Beyond.

## Overview

A battle record management tool for players of the competitive card game "Shadowverse: Worlds Beyond." It provides deck management, match recording, statistical analysis, and streamer overlay features.

## Features

| Feature | Description |
|---------|-------------|
| Match Recording | Record wins/losses, decks used, and opponent classes |
| All Match Formats | Ranked, Grand Prix, Room Match, 2Pick, etc. |
| Statistical Analysis | Win rate, class-based analysis, matchup charts |
| Streamer Mode | OBS overlay, session management |
| Sharing | Public profiles |
| OAuth Authentication | Login via Google/Discord |

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend | Laravel | 12.x |
| PHP | PHP | 8.2+ |
| Frontend | Alpine.js + Blade | 3.x |
| CSS | Tailwind CSS | 4.0 |
| Build | Vite | 7.x |
| Database | PostgreSQL | Supabase |
| Auth | Laravel Socialite | 5.x |
| Deploy | Cloud Run + GitHub Actions | GCP |

## Quick Start (Docker)

### Prerequisites

- Docker Desktop

### Launch

```bash
# Start the project
cd ~/work/projects/shadova-log-app
docker compose up -d
```

### Access

- App: http://shadova.localhost
- Vite (HMR): http://localhost:5173

### Commands

```bash
# Start
docker compose up -d

# View logs
docker compose logs -f app

# Run Artisan commands
docker compose exec app php artisan migrate

# Stop
docker compose down
```

## Local Development (Not Recommended)

### Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 20+
- PostgreSQL (or Supabase)

### Setup

```bash
# Clone the repository
git clone <repository-url>
cd shadova-log-app

# Setup (install dependencies, generate key, migrate, build)
composer setup

# Or run individually
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Environment Variables

Set the following in the `.env` file:

```bash
# Database (Supabase)
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=xxxxx

# Application
APP_URL=http://localhost:3000

# OAuth (optional)
GOOGLE_CLIENT_ID=xxxxx
GOOGLE_CLIENT_SECRET=xxxxx
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback

DISCORD_CLIENT_ID=xxxxx
DISCORD_CLIENT_SECRET=xxxxx
DISCORD_REDIRECT_URI=${APP_URL}/auth/discord/callback
```

For production on Cloud Run, use `deploy/google/shadova.runtime.env.example` as the base runtime env file.

### Start Development Server

```bash
# Start all services simultaneously (recommended)
composer dev

# Or start individually
php artisan serve --port=3000  # Backend
npm run dev                     # Frontend (Vite)
```

## Command Reference

| Command | Description |
|---------|-------------|
| `composer dev` | Start development server (all services) |
| `composer test` | Run PHPUnit tests |
| `composer setup` | Initial setup |
| `php artisan migrate` | Run migrations |
| `php artisan migrate:fresh --seed` | Reset DB + seed |
| `npm run build` | Production build |

## Documentation

See [docs/](./docs/) for detailed documentation.

| Document | Contents |
|----------|----------|
| [System Overview](./docs/02-architecture/system-overview.md) | Architecture, tech stack |
| [Database Design](./docs/04-data/db-schema.md) | Table definitions, ER diagrams |
| [API Reference](./docs/06-interfaces/api-reference.md) | All route specifications |
| [Feature Design](./docs/05-features/feature-design.md) | Detailed feature design |
| [Environment Setup](./docs/08-deployment/environment-setup.md) | Development environment setup |
| [Deployment Guide](./docs/08-deployment/deployment.md) | Cloud Run / Supabase deployment |

## Project Structure

```
shadova-log-app/
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/              # Eloquent models
│   ├── Policies/            # Authorization policies
│   └── Notifications/       # Notification classes
├── resources/
│   ├── views/               # Blade templates
│   └── js/                  # JavaScript (Alpine.js)
├── database/
│   ├── migrations/          # DB migrations
│   ├── factories/           # Model factories
│   └── seeders/             # Seeders
├── routes/
│   └── web.php              # Web route definitions
├── docs/                    # Documentation
└── tests/                   # PHPUnit tests
```

## Deployment

- **Platform**: Google Cloud Run
- **Branch**: `main`
- **CI/CD**: GitHub Actions (`.github/workflows/deploy-google.yml`)

## License

MIT License
