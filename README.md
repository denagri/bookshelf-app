# Bookshelf App (書籍管理システム)

本プロジェクトは、Laravel 10.x と Laravel Sail (Docker) をベースに構築された書籍管理アプリケーションです。
フロントエンドには Tailwind CSS、データベース管理には phpMyAdmin を採用しています。

---

##  使用技術 (実行環境)

* **PHP**: 8.5.7 (Laravel Sailコンテナ)
* **Framework**: Laravel 10.50.2
* **Database**: MySQL 8.4.10
* **Tools**: Laravel Sail, phpMyAdmin

---

##  開発環境の構築手順 (Setup)

以下の手順に従って、上から順に環境構築を行ってください。

### 1. プロジェクトの作成と移動
最新版のLaravelではなく、10.50.2 を明示的に指定してプロジェクトを作成します。

```bash
# プロジェクトの作成（Laravel 10系）
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html -e COMPOSER_CACHE_DIR=/tmp/composer_cache laravelsail/php82-composer:latest composer create-project laravel/laravel:^10.0 bookshelf-app

# プロジェクトのフォルダへ移動
cd bookshelf-app

# Laravel Sail のインストール
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html -e COMPOSER_CACHE_DIR=/tmp/composer_cache laravelsail/php82-composer:latest composer require laravel/sail --dev

# Sailの設定ファイルを生成（MySQLを選択）
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html -e COMPOSER_CACHE_DIR=/tmp/composer_cache laravelsail/php82-composer:latest php artisan sail:install --with=mysql

```

>  **M1/M2/M3 Mac (Apple Silicon) をお使いの方へ**
> この後の手順で `sail up -d` を実行した際、`no matching manifest for linux/arm64/v8` エラーが発生した場合は、生成された `compose.yaml` 内の `mysql` サービスに以下の一行を追加してください。
> ```yaml
> platform: 'linux/amd64'
> ```

### 3. 周辺ツールの追加 (phpMyAdmin)
`compose.yaml` を開き、`mysql` サービスの直後に以下の設定を追加してください。

```yaml
phpmyadmin:
    image: 'phpmyadmin:latest'
    ports:
        - '\${FORWARD_PHPMYADMIN_PORT:-8080}:80'
    environment:
        PMA_HOST: mysql
        PMA_USER: '\${DB_USERNAME}'
        PMA_PASSWORD: '\${DB_PASSWORD}'
    networks:
        - sail
    depends_on:
        - mysql
```

### 4. 環境変数の設定 (.env)
`.env` ファイルを開き、データベース接続情報が以下と一致していることを確認・修正します。
`DB_HOST` は `127.0.0.1` ではなく、Dockerコンテナ名である `mysql` を指定する必要があります。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### 5. コンテナの起動とエイリアスの設定
```bash
# Sailをバックグラウンドで起動
./vendor/bin/sail up -d

# エイリアスを設定して 'sail' だけでコマンドを実行できるようにする (zshの場合)
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc

# シェルを再起動してエイリアスを有効にする
exec \$SHELL
```
※これ以降の手順は、エイリアス設定後の `sail` コマンド表記で記載しています。

### 6. アプリケーションキーの生成
```bash
sail artisan key:generate
```

### 7. フロントエンド (Tailwind CSS) のセットアップ
```bash
# 1. NPM依存パッケージのインストール (Sailコンテナが起動していること)
sail npm install

# 2. Alpine.jsのインストール
sail npm install alpinejs

# 3. Tailwind CSSと @tailwindcss/forms プラグインのインストール
sail npm install -D tailwindcss@^3.4.0 @tailwindcss/forms postcss autoprefixer

# 4. 設定ファイルの生成
sail npx tailwindcss init -p
```

#### Tailwind設定ファイルの修正
`tailwind.config.js` を以下の内容で上書きしてください。
```javascript
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms],
};
```

#### リソースファイルの入れ替えとサーバー起動
1. 本プロジェクトの `resources` ファイルを、指示された `coachtech-prepared-file/Preparedblade-mockcase-BookShelf` リポジトリの `Basic` ブランチ のファイルと入れ替えます。
2. 入れ替え後、以下のコマンドでVite開発サーバーを起動します。

```bash
# Vite開発サーバーの起動 (開発中は常にこのコマンドを実行した状態にしてください)
sail npm run dev
```

### 8. データベースの構築 (マイグレーション & シード)
テーブルを作成し、初期データを投入します。

```bash
# マイグレーションとシードの実行
sail artisan migrate --seed
```
* ※既存のデータベースをリセットして再構築したい場合は以下を実行してください。
  ```bash
  sail artisan migrate:fresh --seed
  ```

---

##  ユーザーログイン方法 (テスト用アカウント)

* **Name**: テストユーザー
* **Email**: `test@example.com`
* **Password**: `password`

---

##  画面URL一覧

ローカル環境起動後、以下のURLから各画面にアクセスできます。

| 画面名 | URL |
| :--- | :--- |
| **書籍一覧（トップ）** | http://localhost/ |
| **書籍詳細** | http://localhost/books/{book} |
| **書籍登録** | http://localhost/books/create |
| **書籍編集** | http://localhost/books/{book}/edit |
| **ジャンル一覧** | http://localhost/genres |
| **ジャンル詳細** | http://localhost/genres/{genre} |
| **ジャンル登録** | http://localhost/genres/create |
| **ジャンル編集** | http://localhost/genres/{genre}/edit |
| **レビュー編集** | http://localhost/reviews/{review}/edit |
| **お気に入り一覧** | http://localhost/favorites |
| **ランキング** | http://localhost/ranking |
| **ログイン** | http://localhost/login |
| **会員登録** | http://localhost/register |
| **phpMyAdmin** | http://localhost:8080/ |

---

##  データベース設計 (ER図)

![alt text](<スクリーンショット 2026-08-08 092816.png>)

---

##  セキュリティに関する重要注意（日本語化について）

本プロジェクトの日本語化（バリデーション・認証メッセージ）は、安全性を考慮し**手動配置**で行います。

1. `config/app.php` の `locale` を `ja` に変更する。
2. `lang/ja/` ディレクトリを作成し、必要なメッセージファイルを手動で配置する。

`laravel-lang/lang` などの外郭パッケージ（`composer require laravel-lang/...`）は**絶対に導入しないでください**。これらは過去にサプライチェーン攻撃によるマルウェア配布に悪用された経緯があるため、本プロジェクトでは禁止としています。
