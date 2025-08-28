# SlimFrame

Lightweight PHP project scaffold with a minimal router, controllers, and a simple SQL-based migration runner. this is mean to be a simple yet powerfull starting point.

## What this is not

- This is not a full framework. It is not intended to be a second Laravel, Symfony, or similar — there are no heavy abstractions, service containers, or elaborate ORMs here.
- This is not meant to be a composer-heavy, dependency-packed app. The goal is minimal external dependencies so the project stays fast and easy to inspect.
- This is not a production-ready migration platform with rollbacks, complex SQL parsing, or multi-environment orchestration out of the box. Use a dedicated migration tool for advanced workflows.

This project is intentionally simple: a fast PHP starting point you can understand, modify, and deploy without a large framework or many runtime dependencies.

## Quick overview
- Docker Compose for local development: `docker-compose.yml` (services: `mysql`, `php`, `nginx`)
- Migrations directory: `migrations/`
- Migration runner: `migrate.php` (CLI)

## Requirements
- Docker & docker-compose (recommended for local development)
- PHP CLI (optional if you run migrator on host)
- Composer dependencies are expected under `vendor/` in this repo (already present).

## Getting started (recommended: Docker)

1. Start containers:

```
docker-compose up -d --build
```

2. Run the migration runner from the `php` container (recommended because the service name `mysql` resolves on the Docker network):

```
docker-compose run --rm php php /var/www/html/migrate.php
```

3. Verify tables exist (run in the mysql container):

```
docker-compose exec mysql mysql -u seoanchor -pseoanchor -e "USE seoanchor; SHOW TABLES;"
```

4. Install composer and generate classes autoloading
```
composer install && composer dump-autoload -o
```

5. Open the app in a browser at `http://localhost` (nginx is mapped to port 80 in `docker-compose.yml`).

## Migrations

- Location: `migrations/`
- Files: plain `.sql` files. Name them with a leading sequence (e.g. `001_create_users.sql`, `002_create_user_role_table.sql`) so they run in order.
- The migrator (`migrate.php`) behavior:
  - Creates a `migrations` table to track applied migration filenames.
  - Skips files already recorded in `migrations`.
  - Skips migrations containing `DROP TABLE` (safety: will not automatically drop tables).
  - Attempts a simple heuristic to add `IF NOT EXISTS` to `CREATE TABLE` statements that lack it (a convenience; review complex SQL manually).
  - Runs each migration inside a transaction and records it after successful execution.

Safety notes:
- The migrator is intentionally conservative. For complex schema changes (ALTER, stored procedures, multiple statements with custom delimiters) review and run manually.
- Always back up production data and use a proper migration system (Phinx, Laravel migrations, Doctrine Migrations) for production workflows.

## Database configuration

Database configuration is centralized under `Config/Database/Connection.php` and `Config/Database/Database.php`.

- `Connection::db()` returns an array of defaults read from environment variables. Defaults in the file:
  - DB_DRIVER: `mysql`
  - DB_HOST: `mysql` (note: Docker service hostname; on the host use `127.0.0.1`)
  - DB_PORT: `3306`
  - DB_NAME, DB_USER, DB_PASSWORD: `seoanchor` by default

- `Database::pdo()` builds a PDO connection for `mysql`, `pgsql`, or `sqlite` using the config and sets safe PDO options (exceptions, default fetch mode, no emulated prepares). It also exposes helper methods: `begin()`, `commit()`, `rollBack()`, `driver()`, and `quoteIdent()`.

If you need to override settings for local development, export environment variables or set them in your Docker `environment` stanza (already done for the `php` service in `docker-compose.yml`).

## How to add a migration

1. Create a new file in `migrations/` with a prefixed sequence number, e.g. `003_add_profiles.sql`.
2. Prefer `CREATE TABLE IF NOT EXISTS` and avoid `DROP TABLE` in automated migrations.
3. Run the migrator (container recommended):

## Router and routes

- `Routes/Router.php` contains a small routing layer used by the application.
- Route definitions live in `Routes/WebRoutes.php` and are initialized from `Routes/init.php`.
- Controllers are in `Controllers/` and generally return rendered views from `views/`.

## Middleware

- Middleware lives under `Middlewares/` and the base helper is `Middlewares/Middleware.php`.
- There are two types of middleware:
  - Global middleware (applied to every route) — registerable via `Middleware::use_middleware()`.
  - Named middleware (registered once and referenced by name) — registerable via `Middleware::register_middleware($name, $callable)`.

- `AuthMiddleware` shows the pattern: it registers a named middleware `auth` that redirects unauthenticated users to `/`.

- In `Routes/init.php` the app calls `AuthMiddleware::register()` to ensure the named middleware is available when routes are defined.

- Attaching middleware to routes:
  - When declaring routes you can pass an array of middleware as the third parameter. The array entries may be:
    - A string referring to a named middleware (e.g. `'auth'`).
    - A callable middleware (e.g. `function(callable $next){ ... }`).

- Example: protect the dashboard route with the named `auth` middleware:

```php
get('/dashboard', 'DashController@index', ['auth']);
```

- Execution order and behavior:
  - The router merges `Middleware::$GLOBAL_MIDDLEWARE` with the route-specific middleware array.
  - Named middleware are resolved to their registered callables before execution.
  - Middleware are executed in order (global first, then route-specific). Each middleware receives a single argument — a `$next` callable — and must call `$next()` to continue the pipeline. If a middleware does not call `$next()` (for example, it redirects), the pipeline stops and the route handler is not executed.

- Error handling:
  - If a named middleware is referenced but not registered, the router returns HTTP 500 with `Unknown middleware: <name>`.
  - If an invalid middleware value is supplied, the router returns HTTP 500 with `Invalid middleware`.

## Troubleshooting common errors

- PDOException: SQLSTATE[HY000] [2002] No such file or directory
  - Cause: PHP tried to connect to a DB host that isn't resolvable from the environment (often `mysql` on host). Solution: run the migrator inside the php container (recommended) or set `DB_HOST=127.0.0.1` and ensure MySQL port is published.

- Base table or view not found
  - Cause: the web app is connected to a database that lacks the expected table (for example, `users` was not created). Solution: run the migrator in the environment the web app uses (see commands above) and verify `SHOW TABLES;` in the target DB.

```
docker-compose run --rm php php /var/www/html/migrate.php
```
---
Generated on: 2025-08-28
