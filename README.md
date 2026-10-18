# Car Showroom — Drupal 11 (Render deployment)

Deployment source for the Car Showroom site. `vendor/`, `web/core/` and
contrib modules/themes are intentionally not committed — they're rebuilt
by `composer install` during the Docker image build from `composer.json`
and `composer.lock`.

## What's in this repo

- `composer.json` / `composer.lock` — exact dependency versions.
- `web/modules/custom/` — custom modules (`carshowroom_ui`, `carshowroom_seo`,
  `carshowroom_setup`).
- `config/sync/` — exported site configuration (content types, views,
  blocks, metatag settings).
- `web/sites/default/files/` — the essential uploaded media (vehicle
  photos, hero carousel images, favicon). Drupal's auto-generated caches
  (`styles/`, `css/`, `js/`, `php/`) are excluded and rebuild on their own.
- `Dockerfile` + `docker/` — the Render build/runtime image.

## Database

The database connection is read from environment variables at runtime
(set in the Render dashboard), not committed anywhere in this repo:

| Variable | Purpose |
|---|---|
| `DB_HOST` | Database host |
| `DB_NAME` | Database name |
| `DB_USER` | Database username |
| `DB_PASSWORD` | Database password |
| `DB_PORT` | Database port (defaults to 3306) |
| `DRUPAL_HASH_SALT` | Random string for Drupal's hash salt |
| `TRUSTED_HOST_EXTRA` | Optional extra hostname to trust, beyond `*.onrender.com` |

## First deploy

Once the container is running against a fresh database, install Drupal
from the exported configuration:

```
drush site:install --existing-config -y
```
