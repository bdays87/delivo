# Delivo — Claude Code guide

This file orients Claude Code when working in **delivorepo**, the monorepo for **Delivo** — a Zimbabwe-focused multi-vendor marketplace.

> Read this first. For deeper agent-facing detail (endpoint tables, conventions cheatsheet) also see `AGENTS.md`. For roadmap and decisions, see `projectplan.md`.

---

## Working agreement (read first)

- **Approval gate.** Delivo follows the Shofe workflow: propose a plan and wait for approval before building. Do NOT scaffold or refactor speculatively. eGP-style "scaffold-fast" does NOT apply here.
- **Layered API rule.** Every endpoint goes Controller → Service → Interface → Repository. Never put business logic in a controller. Bind interfaces in `RepositoryServiceProvider`.
- **Admin sidebar is data-driven.** Sidebar items come from `modules`/`submodules`, filtered by the authenticated user's Spatie permissions via `/api/v1/me`. Do not hard-code admin nav.
- **Address the user as BossBen.** Terse style. No trailing summaries unless asked.

---

## Repository layout

```
delivorepo/
├── delivo/        # Nuxt 4 frontend (storefront, vendor portal, platform admin)
├── delivoapi/     # Laravel 13 API (Sanctum tokens, Spatie permissions)
├── AGENTS.md      # Agent reference (endpoints, conventions, key files)
├── CLAUDE.md      # This file
└── projectplan.md # Roadmap, v1 decisions, slice tracking
```

No root `package.json`. Run commands from the relevant subdirectory.

---

## Product surfaces

1. **Storefront** (`/`) — public B2C/B2B shopping UI. Mostly mock composables today.
2. **Vendor portal** (`/vendor/*`) — sellers with the `vendor` role.
3. **Platform admin** (`/admin/*`) — users with the `admin` role.

**Role precedence after login:** `admin` → `/admin`, then `vendor` → `/vendor`, else customer → `/`.

---

## Stack

### Frontend (`delivo/`)

- Nuxt 4, Vue 3, TypeScript (auto-imports)
- Pinia stores + thin `use*Helper.ts` composables that call the API
- `nuxt-auth-sanctum` in **token mode** (cookie holds the bearer token)
- Tailwind CSS 4 + DaisyUI 5 (semantic tokens only)
- `@nuxt/icon` with Lucide (`lucide:bell`, etc.)
- Yup schemas in `app/utils/*Schemas.ts`

### Backend (`delivoapi/`)

- PHP 8.3, Laravel 13.8
- **MySQL** (locked 2026-05-21) — `.env.example` ships SQLite for quick local, switch to MySQL for real work
- Laravel Sanctum (bearer tokens)
- spatie/laravel-permission — roles: `customer`, `vendor`, `admin`
- Flat folder layout (no `Modules/`) — reuse eGP patterns
- Uniform JSON via `App\Http\Responses\ApiResponse`

---

## Local development

### API

```powershell
cd C:\projects\delivorepo\delivoapi
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve            # http://localhost:8000
```

Seeded admin (local only): `admin@delivo.local` / `Admin12345!`

Seeders: `RolesAndPermissionsSeeder`, `MobileWalletsSeeder`, `CategoriesSeeder`, `ModulesSeeder`, `AdminUserSeeder`.

### Client

```powershell
cd C:\projects\delivorepo\delivo
npm install
npm run dev                  # http://localhost:3000
```

Point client at API via `delivo/.env`:

```
NUXT_PUBLIC_API_BASE=http://localhost:8000
```

Wired in `delivo/nuxt.config.ts` → `runtimeConfig.public.apiBase` and `sanctum.baseUrl`.

---

## API conventions

- Base path: `/api/v1/...` (`delivoapi/routes/api.php`)
- Success shape:
  ```json
  { "status": true, "message": "...", "data": { }, "statusCode": 200 }
  ```
- Errors: `status: false`, appropriate HTTP code, `data` may carry validation errors.
- Login/register also returns top-level `token` (Sanctum plain-text).
- Admin routes: `auth:sanctum` + `role:admin`.

### Endpoint families (implemented)

- **Auth:** `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `GET /me`
- **Vendor (self):** `POST /vendor/apply`, `GET /vendor/me`, `POST /vendor/me/kyc-documents`, `CRUD /vendor/me/payout-accounts` (+ `POST .../primary`)
- **Admin vendors:** `GET /admin/vendors?status=...`, `GET /admin/vendors/{id}`, `POST /admin/vendors/{id}/approve|reject|suspend`, `GET /admin/vendors/{vendor}/kyc-documents/{document}`
- **Admin mobile wallets:** Full CRUD under `/admin/mobile-wallets`; public list at `/mobile-wallets/list`
- **Admin categories:** Full CRUD under `/admin/categories` (archive via DELETE); public list at `/categories/list`

See `AGENTS.md` for the full table.

---

## Frontend patterns Claude should follow

1. **API access path:** add a method to a `use*Helper.ts`, orchestrate in a Pinia store under `app/stores/`, consume from page/component.
2. **Response parsing:** list/detail payloads sit on `response.data` (inside the `ApiResponse` wrapper).
3. **Page guards:** `definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] })` (or `vendor`). Don't use `sanctum:auth` when the custom `auth` middleware is in play — it refreshes identity on hard reload.
4. **After role changes:** call `auth.refresh()` so `roles`/`permissions`/`modules` update without re-login.
5. **UI language:** DaisyUI semantic tokens only (`bg-base-100`, `text-primary`, `bg-neutral`), `rounded-3xl` cards, `rounded-2xl` sub-elements, `rounded-full` pill buttons, hover lift `hover:-translate-y-1 hover:shadow-xl`, container `max-w-7xl mx-auto px-4`, Lucide via `<Icon name="lucide:..." />`, inline SVGs only when DaisyUI/Lucide don't fit.

---

## Backend patterns Claude should follow

```
app/
├── Http/Controllers/Api/{Auth,Admin,Vendor,MobileWallet}/
├── Http/Requests/                  # FormRequest per action
├── Http/Responses/ApiResponse.php
├── Services/                       # business logic lives here
├── Repositories/Eloquent/          # Eloquent implementations
├── Interfaces/Repositories/        # contract per repository
├── Models/
└── Providers/RepositoryServiceProvider.php
```

When adding a repository, bind the interface → implementation in `RepositoryServiceProvider`.

---

## v1 decisions (locked 2026-05-21)

- **Geography:** Zimbabwe only.
- **Currency:** multi-currency USD/ZWG with toggle on the storefront.
- **Payments:** Ecocash, NetOne, Innbucks, Omari. No card processor in v1.
- **Delivery:** admin-fulfilled across Zimbabwe; vendors do NOT self-ship. Catchphrase: "You order. We deliver. Anywhere in Zimbabwe."
- **Products:** single-SKU (no size/colour variants in v1).
- **Pricing:** quantity-tiered per product (`1–9 = $5, 10–49 = $4.50, 50+ = $4`). One tier list serves both B2C and B2B.
- **Reviews:** customer-side product reviews.
- **Cart:** login required (no guest cart).
- **Coupons:** vendor-scoped only.

---

## Domain model (implemented today)

- **Vendor lifecycle:** status `PENDING | ACTIVE | REJECTED | SUSPENDED`; one vendor per user (`owner_user_id` unique); approve sets `status=ACTIVE`, `approved_at=now()`.
- **KYC documents:** `vendor_kyc_documents` (national ID upload during onboarding).
- **Payout accounts:** `vendor_payout_accounts` — supports mobile wallet + bank, ZWG/USD.
- **Mobile wallets:** reference data for Ecocash / NetOne / Innbucks / Omari.
- **Categories:** marketplace taxonomy with optional parent, icon, emoji, tint, sort order, status `ACTIVE|ARCHIVED`.
- **Modules / submodules:** drive the admin sidebar URLs and the Spatie permissions (`can.access.*`). Many submodule URLs are placeholders for unbuilt screens.

---

## What's NOT built yet (do not assume)

- Products, cart, checkout, orders, payments, shipping desk, reviews, disputes, payouts.
- Storefront vendor/product data is mock composables (`useFeaturedVendors`, `useBestDeals`, etc.), not API-driven.
- Admin dashboard GMV / orders / dispute KPIs are placeholders.
- Vendor notification bell uses dummy data.
- `GET /api/v1/admin/dashboard/stats` does NOT exist — compute from list endpoints or add one deliberately.

---

## Quality

- API: `cd delivoapi && php artisan test` (PHPUnit), `vendor/bin/pint` for style.
- Client: no test suite yet.
- Migrations live in `delivoapi/database/migrations/`. After schema changes: `php artisan migrate` (re-seed if modules/roles changed).

---

## Git / workflow

- Two apps, often touched together for a vertical slice.
- Do not commit unless BossBen asks.
- Never commit `.env` or secrets.
- Prefer minimal diffs; do not refactor surrounding code while implementing a slice.

---

## Quick reference — key files

| Concern | File |
|---------|------|
| API routes | `delivoapi/routes/api.php` |
| Nuxt + Sanctum config | `delivo/nuxt.config.ts` |
| Auth store | `delivo/app/stores/auth.ts` |
| Vendor model / statuses | `delivoapi/app/Models/Vendor.php` |
| Admin vendor approval | `delivoapi/app/Services/AdminVendorService.php` |
| Admin dashboard stats | `delivo/app/pages/admin/index.vue` |
| Vendor layout | `delivo/app/layouts/vendor.vue` |
| Repository bindings | `delivoapi/app/Providers/RepositoryServiceProvider.php` |
