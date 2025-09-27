# Gacha Lite 🎲

Laravel + Next.js を使ったシンプルなガチャアプリです。  
API 化した Laravel バックエンドと、Next.js フロントエンドでグラフ表示まで対応しています。

---

## 🔧 技術スタック
- **Backend**: Laravel 12 (PHP 8.3, SQLite/MySQL)
- **Frontend**: Next.js 14 (TypeScript, React, TailwindCSS, Recharts)
- **Container**: Docker / Docker Compose (nginx, php, mysql, node)

---

## 🚀 機能一覧
- ガチャ抽選機能（レア度ごとの排出確率調整あり）
- 抽選履歴の保存（ユーザー名 or セッションキー）
- 履歴の CSV 出力
- ランキング機能（回数 / UR枚数 / 図鑑完成率）
- API エンドポイント
  - `POST /api/gacha` : ガチャを引く
  - `GET  /api/history` : 抽選履歴を取得
  - `GET  /api/ranking` : ランキングを取得
- フロント（Next.js）
  - `/` : バーグラフで回数ランキング表示

---

## 📌 開発用URL一覧（ローカル環境）

> まず Laravel サーバーを起動してください。  
> APP_URL（例: `http://127.0.0.1:8000`）＋下記のパスでアクセスできます。

- トップページ（ガチャ） → `/`
- 履歴 → `/history`
- 図鑑 → `/catalog`
- ランキング → `/ranking`
- 管理：アイテム一覧 → `/admin/items`
- 管理：新規アイテム作成 → `/admin/items/create`
- 管理：レア度倍率（バランス調整） → `/admin/balance`
- 管理：履歴CSVエクスポート → `/admin/history/export`
- ユーザー名設定 → `/name`

---


## 📦 セットアップ

### 1. Laravel (バックエンド)
```bash
cd gacha-lite
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve


cd gacha-frontend
cp .env.example .env.local
npm install
npm run dev

NEXT_PUBLIC_API_BASE=http://127.0.0.1:8000
