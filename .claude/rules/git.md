# Gitワークフロー規約

## ブランチ戦略

### ブランチ構成

| ブランチ | 用途 | マージ元 |
|----------|------|----------|
| `main` | 安定版（本番環境） | develop |
| `develop` | 開発版（統合ブランチ） | feature/* |
| `feature/*` | 機能開発用（任意） | - |

### 基本ルール

1. **開発作業は必ず `develop` ブランチで行う**
2. **`main` ブランチへの直接コミットは禁止**
3. 安定版リリース時のみ `develop` → `main` へマージ

## コミット規約

### コミットメッセージ形式

```
<type>: <subject>

<body>

Co-Authored-By: Claude Opus 4.5 <noreply@anthropic.com>
```

### Type一覧

| Type | 用途 |
|------|------|
| `feat` | 新機能追加 |
| `fix` | バグ修正 |
| `docs` | ドキュメントのみの変更 |
| `style` | コードの意味に影響しない変更（空白、フォーマット等） |
| `refactor` | リファクタリング（機能追加・バグ修正を含まない） |
| `test` | テストの追加・修正 |
| `chore` | ビルドプロセスや補助ツールの変更 |

### コミットメッセージ例

```bash
# 良い例
feat: GitHub Issue連携のフィードバック機能を追加
fix: 対戦記録の保存時にエラーが発生する問題を修正
docs: 環境構築ガイドを更新

# 悪い例
update files
fix bug
WIP
```

## 作業手順

### 新機能開発時

```bash
# 1. developブランチに切り替え
git checkout develop

# 2. 最新を取得
git pull origin develop

# 3. 作業・コミット
git add <files>
git commit -m "feat: 機能の説明"

# 4. プッシュ
git push origin develop
```

### 安定版リリース時

```bash
# 1. mainブランチに切り替え
git checkout main

# 2. developをマージ
git merge develop

# 3. プッシュ
git push origin main

# 4. developに戻る
git checkout develop
```

## 禁止事項

- `main` ブランチへの直接コミット
- `git push --force` の使用（特にmain/develop）
- 機密情報（.env、APIキー等）のコミット
- 巨大なバイナリファイルのコミット

## Claude Code使用時の注意

1. **ブランチ確認**: 作業前に現在のブランチを確認
2. **コミット前確認**: `git status` と `git diff` で変更内容を確認
3. **Co-Authored-By**: コミットメッセージに必ず含める
4. **プッシュ前確認**: ユーザーの明示的な指示がない限りプッシュしない
