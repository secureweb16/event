# Laravel Event App

Simple Laravel + Vue + Inertia project for event listing and attendee registration.

## Requirements

- PHP 8.3+
- Composer
- Node.js + npm
- MySQL

## Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Create `.env` file:

```bash
copy .env.example .env
```

4. Generate app key:

```bash
php artisan key:generate
```

5. Update database values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravelai
DB_USERNAME=root
DB_PASSWORD=
```

6. Run migrations:

```bash
php artisan migrate
```

7. Seed sample data:

```bash
php artisan db:seed
```

If you want a fresh database with sample data again:

```bash
php artisan migrate:fresh --seed
```

## Run Project

Run everything together:

```bash
composer dev
```

This starts:

- Laravel server
- Queue listener
- Vite dev server

## Manual Run

If you want to run services separately:

```bash
php artisan serve
```

```bash
npm run dev
```

```bash
php artisan queue:listen
```