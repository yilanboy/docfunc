<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://blobs.docfunc.com/docfunc-dark-badge.png" width="30%">
    <img alt="Badge changing depending on mode." src="https://blobs.docfunc.com/docfunc-light-badge.png" width="30%">
  </picture>
</p>

<p align="center">
  <img src="https://github.com/YilanBoy/docfunc/actions/workflows/tests.yaml/badge.svg" alt="Tests">
  <a href="https://codecov.io/gh/YilanBoy/docfunc" >
    <img src="https://codecov.io/gh/YilanBoy/docfunc/graph/badge.svg?token=K2V2ANX2LW" alt="Codecov"/>
  </a>
</p>

## Introduction

docfunc is a personal blog built on Laravel 13, Livewire 4, and Tailwind CSS 4, used as a Laravel learning playground and powered by CKEditor 5 for authoring, Algolia for full-text search, and AWS S3 for image uploads.

## Features

- **Posts** — CKEditor 5 authoring with Shiki syntax highlighting, S3 image uploads, soft deletes with mass-prune, RSS feed via `spatie/laravel-feed`, and Algolia full-text search.
- **Comments** — Hierarchical threads with queued email and database notifications.
- **Authentication** — Login, registration, email verification, and account deletion via signed mail.
- **WebAuthn passkeys** — Register, manage, and sign in with passkeys.
- **Tags & Categories** — Tag input via Tagify with many-to-many tags and per-post categories.
- **oEmbed** — Twitter and YouTube oEmbed proxy endpoints.
- **Bot protection** — Cloudflare Turnstile on public forms.
- **Settings** — Cached application settings via a `Setting` model and service.

## Tech stack

### Backend

- PHP `^8.4`
- Laravel 13
- Laravel Octane 2
- Laravel Sanctum 4
- Laravel Scout 10

### Frontend

- Livewire 4 with `livewire/blaze`
- Tailwind CSS 4 (via `@tailwindcss/vite`) and `@tailwindcss/typography`
- Vite 8
- TypeScript 5
- CKEditor 5
- Shiki
- Tagify (`@yaireo/tagify`)
- `@simplewebauthn/browser`

### Integrations

- Algolia
- AWS S3
- Cloudflare Turnstile
- Bref (AWS Lambda)

## Requirements

- PHP `^8.4`
- Composer
- Node.js and npm

> [!NOTE]
> SQLite is the default database. Any Laravel-supported database (MySQL, PostgreSQL, etc.) works with a few env tweaks.

## Installation

Clone the repository:

```sh
git clone https://github.com/YilanBoy/docfunc.git
cd docfunc
```

Install PHP dependencies:

```sh
composer install
```

Copy the example env file:

```sh
cp .env.example .env
```

Generate the application key:

```sh
php artisan key:generate
```

Create the SQLite database file (skip if you're not using the default SQLite driver):

```sh
touch database/database.sqlite
```

Run migrations:

```sh
php artisan migrate
```

Install JavaScript dependencies:

```sh
npm install
```

Start the Vite dev server (or `npm run build` for a production bundle):

```sh
npm run dev
```

Start the Laravel dev server:

```sh
php artisan serve
```

> [!NOTE]
> `composer create-project` would auto-run env copy, key generation, and migrations via composer scripts. A plain `git clone` does not, which is why these steps are spelled out.

## Configuration

- **Database** — defaults to SQLite (`database/database.sqlite`). Switch via `DB_CONNECTION` and the standard `DB_*` vars in `.env`.
- **Mail** — defaults to `MAIL_MAILER=log`. Set SMTP vars for real delivery.
- **Filesystem & S3** — `FILESYSTEM_DISK=local` by default. CKEditor image uploads use S3; set `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`.
- **Algolia / Scout** — set `ALGOLIA_APP_ID`, `ALGOLIA_SECRET`. `SCOUT_PREFIX` namespaces indexes per environment.
- **Cloudflare Turnstile** — set `CAPTCHA_SITE_KEY` and `CAPTCHA_SECRET_KEY`. Default values in `.env.example` are Cloudflare's "always pass" test keys.
- **Octane** — `OCTANE_SERVER` accepts `swoole`, `roadrunner`, or `frankenphp`. Default in `.env.example` is `swoole`.

## Development

- `php artisan serve` — dev server.
- `npm run dev` — Vite dev server with HMR.
- `php artisan octane:start` — production-style server (uses `OCTANE_SERVER`).
- `php artisan pail` — tail application logs.
- `php artisan ide-helper:generate` and `php artisan ide-helper:models` — generate IDE autocomplete metadata.

## Testing & code quality

The project uses Pest 4 (with `pest-plugin-browser` powered by Playwright), Larastan/PHPStan at level 5, and Laravel Pint. The one-shot CI script runs all three:

```sh
composer ci
```

Or run them individually:

```sh
php artisan test --parallel    # or: vendor/bin/pest --parallel
vendor/bin/phpstan analyse --memory-limit=2G
vendor/bin/pint --parallel
```

> [!NOTE]
> Tests run against an in-memory SQLite database with Scout's `collection` driver (configured in `phpunit.xml`).

## Deployment

### Octane

docfunc runs on [Laravel Octane](https://laravel.com/docs/octane), which supports three application servers — Swoole, RoadRunner, and FrankenPHP. Pick one via `OCTANE_SERVER` and start it with:

```sh
php artisan octane:start --server=$OCTANE_SERVER --host=0.0.0.0 --port=8000
```

> [!NOTE]
> Install Swoole via PECL (`pecl install swoole`) or apt (`sudo add-apt-repository ppa:ondrej/php` then `sudo apt-get install php8.4-swoole`). For the other servers, see <https://roadrunner.dev> and <https://frankenphp.dev>.

### Supervisor

Octane worker (`/etc/supervisor/conf.d/docfunc-octane-worker.conf`):

```text
[program:docfunc-octane-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/docfunc/artisan octane:start --server=<swoole|roadrunner|frankenphp> --host=0.0.0.0 --port=8000
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/docfunc-octane-worker.log
stopwaitsecs=3600
```

Queue worker (`/etc/supervisor/conf.d/docfunc-queue-worker.conf`):

```text
[program:docfunc-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/docfunc/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/docfunc-queue-worker.log
stopwaitsecs=3600
```

### Scheduler

Edit the crontab:

```sh
crontab -e
```

Add the following entry to run the [Laravel scheduler](https://laravel.com/docs/scheduling) every minute:

```text
* * * * * cd /var/www/docfunc && php artisan schedule:run >> /dev/null 2>&1
```

### Serverless (Bref / AWS Lambda)

The project bundles `bref/bref` and `bref/laravel-bridge` for AWS Lambda. Production runs as Lambda function `docfunc-production-web` in `us-west-2`. See <https://bref.sh> for setup.

## CI

- **Tests** (`.github/workflows/tests.yaml`) — runs on PRs and pushes to `main`, matrix over PHP 8.4 and 8.5; runs PHPStan, Pint (`--test`), and Pest with coverage uploaded to Codecov; sends status to Telegram.
- **Maintenance toggle** (`.github/workflows/maintenance-mode-toggle.yaml`) — manual dispatch; toggles `MAINTENANCE_MODE` on the `docfunc-production-web` Lambda function.
