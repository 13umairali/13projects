# DahiMail — repo notes

`website/` is a Laravel 12 app (web UI + API + queue, Pest tests). `flutter_app/` is the
mobile client: it needs an emulator and is not part of the sandbox stack.

## Running it

```bash
docker compose -f docker-compose.base44.yml up -d --build
```

Preview on http://localhost:3000. Services: `db` (MariaDB 11), `setup` (one-shot: composer
install, APP_KEY, migrations, seed), `app` (Laravel dev server on 8000 → host 3000),
`assets` (Vite build watcher), `worker` (`queue:listen`).

Everything the app is configured with arrives as container environment variables from the
compose file. `website/.env` deliberately holds **only** the generated `APP_KEY` —
Laravel's immutable dotenv lets real environment variables win over that file, so keep it
that way. The DB credentials are generated for this sandbox; **no external service
credentials are needed to boot** — AI/mail/OAuth/payment integrations are configured in the
admin panel (settings are read from the database, `AppServiceProvider::applyAdminMailSettings`).

Seeded logins: `admin@mailtrixy.com` / `password123` (Super Admin), `demo@mailtrixy.com` / `12345678`.

## Non-obvious things that break this setup

- **`php artisan serve` must run with `--no-reload`.** Without it Laravel forwards only a
  small allowlist (`APP_ENV`, `PATH`, …) of environment variables to the PHP server child
  process, so `DB_CONNECTION` never reaches the web requests: the app falls back to
  config's `sqlite` default and every page answers 500 with
  `Database file at path [/app/database/database.sqlite] does not exist`. The flag also
  makes `PHP_CLI_SERVER_WORKERS` effective.
- **`BASE44_PREVIEW_MODE=1` (read in `AppServiceProvider::configurePublicDiskUrl`) forces
  https on generated URLs.** The preview is served over HTTPS through a reverse proxy
  Laravel cannot detect, so `url()`/`asset()` otherwise emit `http://` links that the
  browser blocks as mixed content — the page renders completely unstyled. Unset or any
  value other than `1`, the normal scheme detection applies.
- **Some incremental migrations re-create objects the consolidated `0001_01_01_*`
  migrations already created** (e.g. `email_signatures`). The app's own web installer skips
  those duplicates and the `setup` service does the same: on an
  "already exists" / "Duplicate column" / "Duplicate key" error it marks that one migration
  as run and retries. After adding a migration, re-run:
  `docker compose -f docker-compose.base44.yml run --rm setup`.
- `app` waits for `setup` to exit 0, and the `app` command waits for the Vite manifest from
  `assets` before starting.

## Tests

`cd website && php artisan test` (Pest) — needs the `db` service. In this sandbox run a
single file rather than the whole suite; CI runs the full suite.
