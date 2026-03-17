# 00 — Project Setup & Environment

> **Goal:** Bootstrap the Laravel 11 project with all required packages, environment configuration, and base infrastructure before writing any feature code.

---

## Micro Tasks

### MT-000.1 — Create Laravel 11 Project

- [ ] Run `composer create-project laravel/laravel:^11 hms`
- [ ] Set PHP version constraint to `>=8.3` in `composer.json`
- [ ] Verify `php artisan --version` returns `11.x`

---

### MT-000.2 — Configure Environment

- [ ] Copy `.env.example` to `.env`
- [ ] Set `APP_NAME=HMS`
- [ ] Set `APP_ENV=local` (production for live)
- [ ] Set `APP_DEBUG=true`
- [ ] Set `APP_URL=http://localhost`
- [ ] Configure `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT=3306`, `DB_DATABASE=hms`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] Configure Redis: `REDIS_HOST=127.0.0.1`, `REDIS_PORT=6379`
- [ ] Set `CACHE_DRIVER=redis`
- [ ] Set `QUEUE_CONNECTION=redis`
- [ ] Set `SESSION_DRIVER=redis`
- [ ] Run `php artisan key:generate`

---

### MT-000.3 — Install Required Packages

```bash
# Core packages
composer require spatie/laravel-permission
composer require barryvdh/laravel-dompdf
composer require livewire/livewire
composer require laravel/sanctum

# Dev packages
composer require --dev laravel/telescope
composer require --dev barryvdh/laravel-debugbar
```

- [ ] Publish Spatie permission migrations: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- [ ] Publish Telescope: `php artisan telescope:install`
- [ ] Publish DomPDF config: `php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"`

---

### MT-000.4 — Database Setup

- [ ] Create MySQL database `hms`
- [ ] Run `php artisan migrate` to run default Laravel migrations
- [ ] Confirm `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs` tables exist

---

### MT-000.5 — Configure Auth

- [ ] Use Laravel Fortify or built-in Auth scaffold
- [ ] Run `php artisan make:auth` (or configure Fortify)
- [ ] Ensure login/logout routes are registered
- [ ] Add auth middleware to protected routes

---

### MT-000.6 — Redis Configuration

- [ ] Install Redis PHP extension: `pecl install redis`
- [ ] Verify Redis connectivity: `php artisan tinker` → `Redis::ping()`
- [ ] Set up Redis queues: `php artisan queue:work` (background)

---

### MT-000.7 — Queue and Scheduler

- [ ] Register `App\Console\Kernel` for scheduled tasks
- [ ] Configure supervisor or cron for queue worker:
  ```
  * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Add queue worker config to supervisor or `Procfile`

---

### MT-000.8 — Logging and Telescope

- [ ] Set `LOG_CHANNEL=stack` in `.env`
- [ ] Enable Telescope only in local env (check `TelescopeServiceProvider`)
- [ ] Remove Telescope from production autoload

---

### MT-000.9 — Base Application Config

- [ ] Set timezone: `APP_TIMEZONE=Asia/Kolkata` in `.env` and `config/app.php`
- [ ] Set locale: `APP_LOCALE=en`
- [ ] Configure storage symlink: `php artisan storage:link`
- [ ] Set `FILESYSTEM_DISK=local` (or `s3` for production)

---

### MT-000.10 — Version Control Setup

- [ ] Init git: `git init`
- [ ] Add `.gitignore` (Laravel default + add `.env`, `storage/`, `node_modules/`)
- [ ] First commit: `git commit -m "Initial Laravel 11 HMS project setup"`

---

## Output

| Deliverable | Description |
|---|---|
| Laravel 11 installed | Clean project up and running |
| `.env` configured | Database, Redis, App settings |
| All packages installed | Spatie, Livewire, DomPDF, Sanctum, Telescope |
| Database ready | Base migrations run |
| Auth configured | Login/logout working |
| Redis connected | Cache, Queue, Session using Redis |
