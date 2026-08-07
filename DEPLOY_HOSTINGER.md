# Deploy Guide — Hostinger (Laravel 13.24)

Target domain: `https://kerjasama.poltekkesbanten.ac.id`  
Stack: Laravel **13.24** (PHP **≥ 8.3**), Blade + Vite, MySQL/MariaDB, Google OAuth.

## 1. Directory layout (split paths)

Hostinger shared hosting typically exposes only `public_html/` as the web root. Keep the Laravel app **outside** that root:

```
/home/USER/
├── laravel-kerjasama/          ← full Laravel app (composer, app/, storage/, .env)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/                 ← source assets (do not point domain here)
│   ├── storage/
│   │   └── app/
│   │       ├── private/
│   │       │   └── kerjasama/  ← ORIGINAL documents (private_docs disk)
│   │       └── public/
│   │           └── previews/   ← public thumbnails only
│   ├── vendor/
│   └── .env
└── domains/kerjasama.poltekkesbanten.ac.id/
    └── public_html/            ← web root only
        ├── index.php           ← patched bootstrap (see §2)
        ├── build/              ← Vite build output (copied or linked)
        ├── storage -> ../../laravel-kerjasama/storage/app/public
        ├── .htaccess
        ├── favicon.ico
        └── robots.txt
```

Never place `.env`, `vendor/`, `storage/app/private/`, or `app/` under `public_html/`.

## 2. `public_html/index.php` bootstrap paths

Copy `laravel-kerjasama/public/index.php` and `public/.htaccess` into `public_html/`, then adjust relative paths so they point **up** into the app directory.

Example when `laravel-kerjasama/` is a sibling of the domain folder that contains `public_html/`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Path from public_html/ → laravel-kerjasama/
$base = __DIR__.'/../../laravel-kerjasama';

if (file_exists($maintenance = $base.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $base.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $base.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

Adjust `$base` if your Hostinger path depth differs (verify with `dirname(__DIR__)` once after upload).

Also ensure `public_html/.htaccess` forwards non-file requests to `index.php` (Laravel default).

## 3. Private documents vs public previews

| Disk | Config key | Absolute path (app side) | Web-accessible? |
|------|------------|--------------------------|-----------------|
| `private_docs` | `config/filesystems.php` | `laravel-kerjasama/storage/app/private/kerjasama` | **No** — originals streamed only via `DocumentFileController` + policy |
| `public` | previews under `previews/` | `laravel-kerjasama/storage/app/public/previews` | **Yes** — via `/storage/previews/...` |

Originals use UUID filenames and are never linked with `/storage/` for document files. Blade only uses `previewUrl()` for thumbnails.

### Storage link (preferred)

From the app directory:

```bash
php artisan storage:link
```

Then, if the link was created inside `laravel-kerjasama/public/storage`, recreate or symlink into web root:

```bash
# Example: public_html/storage → app public disk
ln -s ../../laravel-kerjasama/storage/app/public storage
```

(Paths relative to `public_html/`.)

### Fallback when symlink is disabled

Copy preview files after each deploy / upload batch:

```bash
rsync -a laravel-kerjasama/storage/app/public/ public_html/storage/
```

Or manually copy `storage/app/public/previews` → `public_html/storage/previews`.  
**Never** copy `storage/app/private/` into `public_html/`.

## 4. Server requirements

- PHP **8.3+** (Laravel 13); enable `openssl`, `mbstring`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- `upload_max_filesize` / `post_max_size` ≥ `DOCUMENT_MAX_UPLOAD_MB` (default 20M)
- No Imagick/poppler required — preview images are uploaded manually by staff

## 5. Environment (`.env` in `laravel-kerjasama/`)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kerjasama.poltekkesbanten.ac.id
APP_KEY=base64:...   # php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database   # or sync if no cron
CACHE_STORE=database
LOG_LEVEL=error

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=465
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@poltekkesbanten.ac.id
MAIL_FROM_NAME="${APP_NAME}"

GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

ADMIN_DEFAULT_PASSWORD=...  # only used by seeders; change immediately after seed
```

Copy from `.env.example` — never commit real secrets.

## 6. Google OAuth callback

In Google Cloud Console → OAuth client:

- Authorized redirect URI: `https://kerjasama.poltekkesbanten.ac.id/auth/google/callback`
- Must match `GOOGLE_REDIRECT_URI` / `APP_URL` exactly (HTTPS, no trailing slash mismatch)

New Google users are always created as **visitor**. Staff roles are assigned only via seeder / SA1 admin UI.

## 7. Frontend asset sync (Vite build → public_html)

Production paths:

```text
~/domains/kerjasama.poltekkesbanten.ac.id/laravel-kerjasama
~/domains/kerjasama.poltekkesbanten.ac.id/public_html
```

**Do not overwrite** custom `public_html/index.php`.

After local visual QA passes, on the server:

```bash
cd ~/domains/kerjasama.poltekkesbanten.ac.id/laravel-kerjasama

# 1) Build Vite assets inside the Laravel app
npm ci
npm run build

# 2) Backup current web-root build
mv ../public_html/build ../public_html/build-backup-$(date +%Y%m%d-%H%M)

# 3) Deploy new build (manifest + hashed CSS/JS)
cp -a public/build ../public_html/build

# 4) Sync static public images used by Blade (logos, hero, icons)
rm -rf ../public_html/images
cp -a public/images ../public_html/images

# 5) Clear / rebuild caches (views + config)
php artisan optimize:clear
php artisan optimize
```

If you deploy code via upload/git without building on the server, run `npm run build` locally then upload both:

- `laravel-kerjasama/public/build` → `public_html/build`
- `laravel-kerjasama/public/images` → `public_html/images`

Also keep a copy of `public/build` inside `laravel-kerjasama/public/build` so app and web root stay aligned.

## 8. Deploy commands (SSH or local then upload)

From `laravel-kerjasama/`:

```bash
composer install --no-dev --optimize-autoloader
# .env already exists on production — do not overwrite casually
npm ci && npm run build
# sync public/build + public/images → public_html (see §7)
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize
```

Ensure `storage/` and `bootstrap/cache/` are writable by the web user.

### Migrate & seed caution

- Prefer `php artisan migrate --force` on every release.
- **Never** run `migrate:fresh` on production.
- **Do not** re-run full `DatabaseSeeder` if live users/docs exist.
- To fix wrong international category names (and ensure years 2026–2020) safely:

```bash
php artisan db:seed --class=CooperationSeeder --force
```

This seeder is idempotent: it renames legacy rows in place (preserves year/document FKs) and does not wipe documents.

- First install only: `php artisan db:seed --force` once (creates SA1/SA2 + hierarchy).
- After seeding, change staff passwords immediately.

### Queue / mail

- Access-request emails are sent after the DB write (failures are caught and do not roll back the request).
- If `QUEUE_CONNECTION=database`, add a Hostinger cron every minute:  
  `cd /home/USER/laravel-kerjasama && php artisan schedule:run`  
  and/or `php artisan queue:work --stop-when-empty` via cron.
- For simplest hosting, set `QUEUE_CONNECTION=sync` and a working SMTP mailer.

## 9. Production checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] HTTPS + `SESSION_SECURE_COOKIE=true`
- [ ] Google OAuth credentials + callback URL
- [ ] SMTP credentials tested (access-request notifications)
- [ ] `storage:link` or copied previews under `public_html/storage`
- [ ] Private docs only under `storage/app/private/kerjasama` (outside web root)
- [ ] Registration routes remain disabled (`routes/auth.php`)
- [ ] Staff passwords changed after seed
- [ ] Permissions: `storage`, `bootstrap/cache` writable; `.env` not web-readable

## 10. Rollback

1. Put site in maintenance (optional): `php artisan down` in app dir.
2. Restore previous app code release (keep current `.env` unless intentionally rolling config).
3. If a migration must be undone and a down method exists:  
   `php artisan migrate:rollback --step=1 --force`  
   Prefer restoring a DB backup taken before migrate for safety.
4. Restore previous `public_html/build` and `index.php` if the web-root bootstrap changed.
5. Clear caches: `php artisan optimize:clear` then re-cache for the restored release.
6. `php artisan up`

Keep a DB dump + copy of `storage/app/private/kerjasama` before risky deploys — private originals are not recoverable from git.

## 11. Security reminders (post-deploy)

- Originals are never served via `/storage/` — only authorized stream routes.
- `Document` model hides `private_file_path` from arrays/JSON.
- SA2 cannot update/delete SA1 (`UserPolicy`).
- Public registration is commented out in `routes/auth.php`.
