# Delivo — Agent Guide

This document helps AI coding agents work effectively in **delivorepo**, a monorepo for **Delivo** — a Zimbabwe-focused multi-vendor marketplace (storefront, vendor portal, platform admin).

## Repository layout

| Path | Role |
|------|------|
| `delivo/` | Nuxt 4 frontend (Vue 3, Pinia, Tailwind 4, DaisyUI) |
| `delivoapi/` | Laravel 13 API (Sanctum token auth, Spatie permissions) |

There is no root `package.json`; run commands from the relevant subdirectory.

## Product surfaces

1. **Storefront** (`/`) — public shopping UI; mostly static/mock composables today (`useFeaturedVendors`, `useBestDeals`, etc.).
2. **Vendor portal** (`/vendor/*`) — sellers with the `vendor` role; layout `app/layouts/vendor.vue`, top nav `app/components/vendor/Topnav.vue`.
3. **Platform admin** (`/admin/*`) — users with the `admin` role; layout `app/layouts/admin.vue`, sidebar driven by modules returned from `/api/v1/me`.

**Role precedence after login:** `admin` → `/admin`, then `vendor` → `/vendor`, else customer → `/`.

## Tech stack

### Frontend (`delivo`)

- **Nuxt 4**, **Vue 3**, **TypeScript** (via Nuxt auto-imports)
- **Pinia** stores + thin **composables** that call the API (`use*Helper.ts`)
- **nuxt-auth-sanctum** — `mode: 'token'`, cookie `sanctum.token.cookie`
- **@nuxt/icon** — Lucide icons (`lucide:bell`, etc.)
- **Tailwind CSS 4** + **DaisyUI 5** (`btn`, `badge`, `dropdown`, `menu`, rounded-3xl cards)
- Validation: **Yup** schemas in `app/utils/*Schemas.ts`

### Backend (`delivoapi`)

- **PHP 8.3**, **Laravel 13**
- **Laravel Sanctum** — API bearer tokens
- **spatie/laravel-permission** — roles: `customer`, `vendor`, `admin`
- Layered architecture: **Controller → Service → Repository (interface-bound)**
- Uniform JSON via `App\Http\Responses\ApiResponse`

## Local development

### API (`delivoapi`)

```bash
cd delivoapi
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate --seed
php artisan serve      # http://localhost:8000
```

`composer run dev` starts server, queue, logs, and Vite together (Laravel default).

**Seeded admin (local only):**

- Email: `admin@delivo.local`
- Password: `Admin12345!`

Seeders: `RolesAndPermissionsSeeder`, `MobileWalletsSeeder`, `ModulesSeeder`, `AdminUserSeeder`.

Default DB in `.env.example`: **SQLite** (`database/database.sqlite`).

### Client (`delivo`)

```bash
cd delivo
npm install
npm run dev            # http://localhost:3000
```

Point the client at the API:

```env
NUXT_PUBLIC_API_BASE=http://localhost:8000
```

Configured in `delivo/nuxt.config.ts` → `runtimeConfig.public.apiBase` and `sanctum.baseUrl`.

## API conventions

- Base path: `/api/v1/...` (see `delivoapi/routes/api.php`)
- Success body shape:

```json
{
  "status": true,
  "message": "Human-readable message",
  "data": { },
  "statusCode": 200
}
```

- Errors: `status: false`, appropriate HTTP code, optional `data` for validation errors
- Login/register may include top-level `token` (Sanctum plain-text token)
- Admin routes: `auth:sanctum` + `role:admin` middleware alias

### Auth endpoints

| Method | Path | Notes |
|--------|------|-------|
| POST | `/api/v1/auth/register` | Returns token; client sets cookie |
| POST | `/api/v1/auth/login` | Sanctum module |
| POST | `/api/v1/auth/logout` | Authenticated |
| GET | `/api/v1/me` | User + `roles`, `permissions`, `modules` tree |

### Vendor (authenticated)

| Method | Path |
|--------|------|
| POST | `/api/v1/vendor/apply` |
| GET | `/api/v1/vendor/me` |
| POST | `/api/v1/vendor/me/kyc-documents` |
| CRUD | `/api/v1/vendor/me/payout-accounts` (+ `POST .../primary`) |

### Admin vendors

| Method | Path |
|--------|------|
| GET | `/api/v1/admin/vendors?status=PENDING\|ACTIVE\|REJECTED\|SUSPENDED` |
| GET | `/api/v1/admin/vendors/{id}` |
| POST | `/api/v1/admin/vendors/{id}/approve` |
| POST | `/api/v1/admin/vendors/{id}/reject` (body: `reason`) |
| POST | `/api/v1/admin/vendors/{id}/suspend` |
| GET | `/api/v1/admin/vendors/{vendor}/kyc-documents/{document}` | Blob stream for KYC preview |

### Admin mobile wallets

Full CRUD under `/api/v1/admin/mobile-wallets`. Public list: `GET /api/v1/mobile-wallets/list` (active wallets for vendor apply).

### Admin categories

Full CRUD under `/api/v1/admin/categories` (archive via `DELETE`). Public list: `GET /api/v1/categories/list` (active categories for storefront). Admin UI: `delivo/app/pages/admin/categories/index.vue`.

## Domain model (implemented)

### Vendor lifecycle

- Status enum: `PENDING` | `ACTIVE` | `REJECTED` | `SUSPENDED`
- One vendor per user (`owner_user_id` unique)
- KYC documents on `vendor_kyc_documents`
- Payout accounts moved to `vendor_payout_accounts` (supports mobile wallet + bank, ZWG/USD)
- Approve sets `status=ACTIVE`, `approved_at=now()`

### Mobile wallets

Reference data for EcoCash/Innbucks/etc.; managed in admin, selected during vendor application.

### Categories

Marketplace taxonomy (`categories` table): `name`, `slug`, optional `parent_id`, `icon`, `emoji`, `tint`, `description`, `sort_order`, `status` (`ACTIVE` \| `ARCHIVED`). Seeded via `CategoriesSeeder`.

### Modules / permissions

- `modules` + `submodules` seed admin sidebar URLs and Spatie permissions (`can.access.*`)
- Frontend reads `auth.user.modules` in `AdminSidebar.vue`
- Many submodule URLs (categories, products, orders, …) are **not built yet** — placeholders in seeder only

## Frontend architecture

### Directory map (`delivo/app`)

```
app/
  assets/css/main.css      # Tailwind + DaisyUI theme
  components/
    admin/                 # Topnav, Sidebar
    storefront/            # Header, Hero, grids, Toasts
    vendor/                # Topnav (welcome, profile, bell)
  composables/
    use*Helper.ts          # Raw useSanctumClient() API calls
    useToast.ts
  layouts/
    default.vue            # Storefront
    admin.vue
    vendor.vue
  middleware/
    auth.ts                # Token refresh before redirect (fixes sanctum race)
    admin.ts
    vendor.ts
  pages/
    index.vue              # Storefront home
    auth/login.vue, register.vue
    admin/                 # Dashboard, vendors, mobile-wallets
    vendor/                # Dashboard, apply
  stores/
    auth.ts                # Pinia over sanctum + register + role helpers
    adminVendor.ts, vendor.ts, adminMobileWallet.ts, ...
  utils/                   # Yup schemas
```

### Patterns agents should follow

1. **API access:** Add methods to `use*Helper.ts`, orchestrate in Pinia `stores/*`, consume from pages/components.
2. **Response parsing:** List/detail data is usually `response.data` (array or object inside the wrapper).
3. **Page guards:** `definePageMeta({ layout: 'admin', middleware: ['auth', 'admin'] })` (or `vendor`). Use `sanctum:auth` only if not using custom `auth` middleware.
4. **Post-role changes:** Call `auth.refresh()` after vendor approval/application so `roles` update without re-login.
5. **UI:** Match existing DaisyUI patterns (rounded-3xl cards, `btn-ghost btn-sm rounded-full`, Lucide via `<Icon name="lucide:…" />`).
6. **Scope:** Prefer minimal diffs; do not commit unless asked; do not commit `.env` or secrets.

### Layouts & navigation

- **Vendor topnav:** Welcome + name, Profile → `/vendor/settings`, Sign out, bell dropdown with dummy notifications (UI only).
- **Admin dashboard** (`pages/admin/index.vue`): KPIs load live vendor counts via `listVendors('ACTIVE'|'PENDING')` — do not hardcode stats.
- **Admin vendors** (`pages/admin/vendors/`): Tabbed list by status; detail page approve/reject/suspend + KYC blob preview.

### Vendor pages — built vs linked only

| Route | Status |
|-------|--------|
| `/vendor`, `/vendor/apply` | Implemented |
| `/vendor/products`, `/orders`, `/coupons`, `/earnings`, `/settings` | Linked in sidebar; pages may not exist yet |
| `/admin/categories` | Implemented (CRUD + archive) |

## Backend architecture

```
app/
  Http/Controllers/Api/{Auth,Admin,Vendor,MobileWallet}/
  Http/Requests/          # Form requests per action
  Http/Responses/ApiResponse.php
  Services/               # Business logic
  Repositories/Eloquent/  # Eloquent implementations
  Interfaces/Repositories/
  Models/
  Providers/RepositoryServiceProvider.php  # Interface → implementation bindings
```

Register new bindings in `RepositoryServiceProvider` when adding repositories.

## Authentication details

- Sanctum **token mode** (not SPA cookie session for API)
- `useAuthStore` wraps `useSanctumAuth`, handles `register()`, `refresh()`, role-based `navigateTo` after login
- `nuxt.config.ts` sets `sanctum.redirect.onLogin: false` so the store controls landing paths
- Custom `auth` middleware refreshes identity when a token cookie exists but `user` is null (fixes hard-refresh race)

## Zimbabwe / marketplace context

- Currency references: **USD** and **ZWG**
- Mobile money wallets as first-class payout method
- Storefront copy references delivery in Zimbabwe (`delivo.co.zw` in vendor slug hints)
- KYC: national ID upload flow during vendor onboarding

## What is not implemented yet (common agent pitfalls)

- Checkout, cart persistence, orders, products, categories (admin URLs exist; APIs/pages largely missing)
- Storefront vendor/product data is mostly **mock composables**, not API-driven
- Admin dashboard GMV, orders, disputes KPIs are placeholders (`0` / static)
- Vendor notification bell uses **dummy data** only
- Do not assume `GET /api/v1/admin/dashboard/stats` exists — compute from list endpoints or add a new endpoint deliberately

## Testing & quality

- API: `cd delivoapi && php artisan test` (PHPUnit)
- API style: `vendor/bin/pint` (Laravel Pint)
- Frontend: no test suite configured in `package.json` yet

## Git / workflow notes

- Two separate apps; changes often touch both `delivo` and `delivoapi` for full features
- Migrations live in `delivoapi/database/migrations/`
- After schema changes: `php artisan migrate` (and seed if modules/roles needed)

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

## Agent checklist for new features

1. Confirm whether the feature is storefront, vendor, or admin scoped.
2. Add API route + request validation + service + repository if backend work is needed.
3. Mirror with `use*Helper` + store + page/component on the client.
4. Use `ApiResponse` helpers; return consistent JSON.
5. Apply correct middleware (`role:admin` on API; `auth` + `admin`/`vendor` on pages).
6. Update this file if you introduce a new cross-cutting convention or major module.
