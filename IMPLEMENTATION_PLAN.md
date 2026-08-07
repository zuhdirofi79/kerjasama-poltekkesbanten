# Implementation Plan — kerjasama.poltekkesbanten.ac.id

## Status: **IMPLEMENTED** (Laravel **13.24**)

Greenfield Laravel app is in place at project root. Security properties, public hierarchy, admin CRUD, Google OAuth (visitor), access requests, private document streaming, and feature tests are complete. See `DEPLOY_HOSTINGER.md` for Hostinger split-path deployment.

---

## 1. Kondisi Existing (Audit — awal)

| Item | Temuan |
|------|--------|
| Project root | Hanya folder `desain/` + PDF desain (9.2 MB) |
| Laravel app | **Tidak ada** — greenfield |
| PHP lokal | 8.3.30 (Laragon) |
| Composer | 2.9.7 |
| Node/npm | v24.14.1 / 11.11.0 |
| Desain PDF | 5 halaman: Home (2 jalur), Dalam Negeri (5 kategori), Luar Negeri (3 program), Year folders RS, Year folders Dinkes |
| Auth/CSS/DB | Belum ada |

**Keputusan:** Buat Laravel baru di root project (pertahankan `desain/`). Tidak ada fitur existing yang perlu dilindungi.

**Stack target (Hostinger-friendly) — delivered:**
- Laravel **13.24** (PHP 8.3+; Hostinger harus PHP ≥ 8.3)
- Blade + Vite + Tailwind CSS
- Laravel Breeze (Blade) untuk session auth admin
- Laravel Socialite untuk Google OAuth visitor
- MySQL/MariaDB (Hostinger default)
- Policies + Gates (tanpa Spatie Permission — mengurangi dependency)
- Queue: `database` / `sync` (dokumentasi cron di `DEPLOY_HOSTINGER.md`)

## 2. Arsitektur Target

```
Public (Blade)          Auth                    Admin (Blade)
──────────────          ────                    ─────────────
/                       Google OAuth (visitor)  /admin/*
/kerjasama/{section}    Session login (admin)   CRUD years/institutions/docs
/kerjasama/.../years    Logout                  Access requests
Document cards          Policy checks           User management (SA1)
  → request flow                                Audit logs

Storage:
  private disk  → storage/app/private/kerjasama/...  (ORIGINAL docs)
  public disk   → storage/app/public/previews/...    (thumbnails only)
  Serve originals ONLY via DocumentFileController + DocumentPolicy
```

## 3. Tabel DB

- `users` — extend: google_id, avatar, role, is_active, softDeletes
- `cooperation_sections`
- `cooperation_categories`
- `year_folders`
- `institution_folders`
- `documents`
- `document_access_requests`
- `audit_logs`

## 4. Roles & Permission Matrix

| Aksi | SA1 | SA2 | Admin | Visitor |
|------|-----|-----|-------|---------|
| CRUD categories/sections | ✓ | — | — | — |
| CRUD year/institution/docs | ✓ | ✓ | ✓ | — |
| Approve/reject/revoke access | ✓ | ✓ | — | — |
| Manage SA2 biodata | ✓ | self limited | — | — |
| Mutate SA1 | self only | **403** | **403** | — |
| Request/view approved docs | — | — | — | ✓ |
| Public browse + thumbnails | ✓ | ✓ | ✓ | ✓ (guest OK) |

## 5. Storage

| Disk | Path | Isi |
|------|------|-----|
| `private_docs` | `storage/app/private/kerjasama` | UUID-named originals |
| `public` | `storage/app/public/previews` | JPG/PNG/WEBP thumbnails |
| Production | App di `laravel-kerjasama/`, web root `public_html/` | Symlink/copy public assets; private tetap di luar web root |

## 6. Routes (ringkas)

Public: `/`, `/kerjasama/{section}`, `/{section}/{category}`, `.../{year}`, `.../{institution}`  
Auth: `/auth/google`, `/auth/google/callback`, `/logout`  
Docs: `POST request-access`, `GET view|download`  
Admin: `/admin` + resources years, institutions, documents, access-requests, users, audit-logs  
Registration: **disabled** in `routes/auth.php`

## 7. Deliverables

Migrations, Models, Policies, Services, Controllers, FormRequests, Seeders, Blade layouts/components/pages, CSS tokens, Feature tests, `.env.example`, `DEPLOY_HOSTINGER.md` — **done**.

## 8. Risiko Deployment Hostinger

- PHP version harus ≥ 8.3 (Laravel 13.24)
- `public_html/index.php` harus bootstrap ke `laravel-kerjasama`
- Imagick/poppler mungkin tidak ada → **manual preview upload wajib**
- Queue worker tidak persistent → sync / cron
- Symlink `storage` mungkin dibatasi → dokumentasikan alternatif copy/link
- Jangan expose `.env`, `vendor`, private storage

## 9. Catatan requirement

- Year seed: **2026–2021** (sesuai PDF; bukan 2020) — years come from DB, not hardcoded in Blade
- Preview wajib sebelum publish
- SA2 tidak boleh mutate SA1 (server-side Policy)
- New Google users = visitor only; scopes `openid profile email`

## 10. Sequence

PHASE 1–19 completed. Remaining work is **ops/production config** (Google credentials, SMTP, Hostinger upload, `APP_DEBUG=false`) — not application code.
