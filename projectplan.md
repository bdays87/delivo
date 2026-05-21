# Delivo — Project Plan

**Owner:** BossBen
**Started:** 2026-05-21
**Repo:** `C:\projects\delivorepo`
**Status doc.** Update as slices land. CLAUDE.md and AGENTS.md cover conventions; this file covers vision, decisions, and slice progress.

---

## 1. Vision

Delivo is a Zimbabwe-focused multi-vendor marketplace (Amazon/eBay-style) supporting both **B2C** (consumers) and **B2B** (bulk/business buyers).

**Catchphrase:** "You order. We deliver. Anywhere in Zimbabwe."

### Monetisation

- Platform takes a **commission on every sale**.
- Vendors register and onboard for **free**.
- Payments are routed through the platform before reaching vendors (escrow-style settlement).

### Roles

| Role | Capability |
|------|-----------|
| **Customer** | Browse, add to cart (login required), checkout, review, dispute. |
| **Vendor** | Apply → KYC → admin approval → list products, set tiered pricing, coupons, view earnings, request categories, manage payout accounts. |
| **Admin** | Approve/reject vendors, moderate listings, manage categories + mobile wallets, run the shipping desk, resolve disputes, trigger payouts. |

---

## 2. v1 decisions (locked 2026-05-21)

| Decision | v1 commitment |
|----------|--------------|
| Geography | Zimbabwe only |
| Currency | USD + ZWG with toggle on storefront |
| Payments | Ecocash, NetOne, Innbucks, Omari (mobile money). No cards in v1. |
| Delivery | **Admin-fulfilled** across Zimbabwe — vendors do NOT self-ship |
| Products | Color variants supported (size deferred). Every product has ≥1 variant row; `color` may be null for "no color choice" products. Stock + SKU live on variant. |
| Pricing | Quantity-tiered per product, stored in USD; converted to ZWG at render via admin-managed rate. Tiers live on parent product (one list across all color variants). Single tier list covers B2C + B2B. |
| Reviews | Customer-side product reviews only |
| Cart | Login required — no guest cart |
| Coupons | Vendor-scoped only (no platform-wide promos) |
| Backend folder layout | Flat — no `Modules/` |
| Database | MySQL |

---

## 3. Stack & locations

| Layer | Stack | Path |
|-------|-------|------|
| Frontend | Nuxt 4 + Vue 3 + TS, Pinia, Tailwind 4 + DaisyUI 5, `nuxt-auth-sanctum` token mode, Lucide via `@nuxt/icon`, Yup | `C:\projects\delivorepo\delivo` |
| Backend | Laravel 13.8 (PHP 8.3), Sanctum tokens, spatie/laravel-permission, MySQL, layered Controller→Service→Interface→Repository, `ApiResponse` helper | `C:\projects\delivorepo\delivoapi` |

Default DB in `.env.example` is SQLite for quick boot; production-shaped work uses MySQL.

---

## 4. Working agreement

- **Approval gate (Shofe-style).** Propose plan, wait for sign-off before building. Do not scaffold speculatively.
- **Slice-by-slice.** Each slice is a thin vertical of API + UI shipped together.
- **Layered API rule** enforced on every endpoint (Controller → Service → Interface → Repository).
- **Admin sidebar is data-driven** from `modules`/`submodules` filtered by user permissions.
- **Minimal diffs.** No unrequested refactors. Don't commit unless asked. Never commit `.env`.

---

## 5. Build slice sequence

Each slice ships API + UI together. Status legend: ✅ done · 🟡 in-progress · ⬜ not started.

| # | Slice | Includes | Status |
|---|-------|----------|--------|
| 1 | **Auth** | Register, login, logout, `/me` with roles+permissions+modules; role-routed redirect | ✅ |
| 2 | **Vendor onboarding** | Vendor apply form, KYC document upload, payout accounts (mobile wallet + bank, ZWG/USD) | ✅ |
| 3 | **Admin vendor moderation** | List by status, detail page, approve / reject / suspend, KYC blob preview | ✅ |
| 4 | **Admin reference data** | Mobile wallets CRUD; Categories CRUD with archive; public list endpoints | ✅ |
| 5 | **Storefront read-only** | Replace mock composables with real category/vendor/product API; product detail page; USD/ZWG toggle; `/products` browse + `/products/[slug]` detail. | ✅ |
| 6 | **Vendor product CRUD** | Product create/edit (color variants + tiered pricing in USD, category, images), submission → admin moderation queue. Edit on ACTIVE flips back to PENDING. | ✅ |
| 7 | **Admin product moderation** | Approve/reject pending products, takedown ACTIVE products. Bundled with USD→ZWG exchange-rate admin. | ✅ |
| 8 | **Cart + checkout** | Login-required cart, address capture, currency-aware totals, place order against mobile wallet payment instruction | ⬜ |
| 9 | **Payment confirmation** | Admin manual confirmation flow per wallet (Ecocash/NetOne/Innbucks/Omari); webhook stubs deferred | ⬜ |
| 10 | **Shipping desk** | Admin order pipeline: confirmed → picked up → out for delivery → delivered | ⬜ |
| 11 | **Reviews** | Customer product reviews on delivered orders | ⬜ |
| 12 | **Disputes** | Customer-raised disputes, admin resolution, refund/hold mechanics | ⬜ |
| 13 | **Vendor earnings + payouts** | Earnings ledger, commission deduction, admin-triggered payout per vendor payout account | ⬜ |
| 14 | **Vendor coupons** | Vendor-scoped coupon CRUD + checkout redemption | ⬜ |
| 15 | **Vendor category requests** | Vendor proposes new categories, admin approves/declines | ⬜ |
| 16 | **Storefront polish** | Replace placeholder copy (payment strip, location, hero, free-shipping line) to match v1 locks | ⬜ |

---

## 6. Domain model — implemented

| Entity | Key columns / notes |
|--------|-------------------|
| `users` | Spatie roles attached; `phone` added 2026-05-21 |
| `vendors` | `owner_user_id` unique, `status` (PENDING/ACTIVE/REJECTED/SUSPENDED), `approved_at` |
| `vendor_kyc_documents` | National ID etc., uploaded during onboarding |
| `vendor_payout_accounts` | Mobile wallet **or** bank, currency (ZWG/USD), `is_primary` |
| `mobile_wallets` | EcoCash/NetOne/Innbucks/Omari reference rows |
| `categories` | `name`, `slug`, optional `parent_id`, `icon`, `emoji`, `tint`, `description`, `sort_order`, `status` |
| `modules` + `submodules` | Drive the admin sidebar; each submodule has a permission like `can.access.vendors` |
| `personal_access_tokens` | Sanctum |
| `permissions` / `roles` (Spatie) | `customer`, `vendor`, `admin` |

Migrations all dated 2026-05-21.

---

## 7. Domain model — to design

These are NOT built. Capture decisions here before writing migrations.

### Product domain (locked 2026-05-21, building now as Slice 6)

- `products` — `vendor_id`, `category_id`, `name`, `slug` (globally unique, name + short hash), `description`, `sku` (optional vendor-level SKU), `weight_kg` (admin shipping), `status` (`PENDING|ACTIVE|REJECTED|ARCHIVED`), `submitted_at`, `approved_at`, `rejected_at`, `rejection_reason`. **Any vendor edit on an `ACTIVE` product flips it back to `PENDING`.**
- `product_images` — `product_id`, `path`, `sort_order`, `is_primary`. Storage: Laravel `public` disk for v1.
- `product_price_tiers` — `product_id`, `min_qty`, `unit_price` (USD). Tiers live on parent (one list across all color variants).
- `product_variants` — `product_id`, `color` (nullable string; null = "no color choice"), `stock_quantity`, `sku` (optional), `image_path` (optional). **Every product has ≥1 variant row.** Stock lives here, not on `products`.
- `exchange_rates` — single active row (`from='USD'`, `to='ZWG'`, `rate`, `updated_at`, `updated_by_user_id`). Admin updates the rate; storefront converts at render.

### Later domains (not yet designed)


- `carts` + `cart_items` (per-user, login-required)
- `addresses` (delivery address attached to user or order)
- `orders` + `order_items` (status pipeline: PENDING_PAYMENT → PAID → PICKED_UP → OUT_FOR_DELIVERY → DELIVERED → COMPLETED; cancellation + refund states)
- `payments` (mobile wallet name, payer reference, amount, currency, confirmed_by admin)
- `reviews` (`product_id`, `user_id`, `order_id`, rating, body)
- `disputes` (`order_id`, raised_by, status, resolution)
- `coupons` (`vendor_id`, code, discount type/value, usage limits, validity window)
- `vendor_earnings_ledger` (gross, commission, net, status PENDING/AVAILABLE/PAID_OUT)
- `payouts` (`vendor_payout_account_id`, amount, currency, status, admin_processed_by)
- `category_requests` (`vendor_id`, proposed name, status)

---

## 8. Placeholder copy to fix before launch

Tracked from the existing landing page (`delivo/app/app.vue`). Conflict with v1 locks — refactor needs BossBen's sign-off:

- Payment strip shows VISA / Mastercard / Apple Pay / Klarna → should be Ecocash / NetOne / Innbucks / Omari.
- Top bar shows USD only → needs USD/ZWG toggle.
- Location says "San Francisco, CA" → should be Zimbabwe (Harare).
- "Free shipping over $35" and "60-minute delivery" copy doesn't match catchphrase.
- Hero headline is "Everything you need, delivered today" — agreed catchphrase isn't surfaced.

---

## 9. Design language (locked)

Driven by `delivo/app/app.vue` landing page. Apply to every new screen.

- DaisyUI semantic tokens only: `bg-base-100`, `bg-base-200`, `text-primary`, `bg-neutral`, `text-primary-content`, `badge-primary`, `btn-primary`.
- Radii: `rounded-3xl` cards, `rounded-2xl` sub-elements, `rounded-full` pill buttons (`btn rounded-full`).
- Hover lift: `hover:-translate-y-1 hover:shadow-xl`.
- Container: `max-w-7xl mx-auto px-4`.
- Icons: Lucide via `<Icon name="lucide:..." />`. Inline SVG only when there's no DaisyUI/Lucide fit.
- Hero/section accents: blurred gradient blobs (`bg-primary/20 blur-3xl`).
- Inter font everywhere.

---

## 10. Known constraints / open questions

- **Payment webhooks:** mobile wallets in Zimbabwe don't all offer reliable webhooks. v1 starts with admin manual confirmation; revisit per-provider as we go.
- **Multi-currency mechanics:** do we store product prices in one currency and convert at render, or store both? Decide before slice 6.
- **Commission rate:** flat % or per-category? Decide before slice 13.
- **Refund flow:** full vs partial refund mechanics not yet designed.
- **Customer KYC for B2B buyers:** TBD whether business buyers need extra verification for credit-style purchasing.

---

## 11. Where we left off (2026-05-21)

- Repo moved to `C:\projects\delivorepo` (was `C:\projects\javascript\delivo*`).
- Slices 1–4 are in. Slice 5 (storefront read-only — wire real category/vendor/product APIs in place of mock composables) is the next candidate, but no API for products exists yet, so Slice 5 will need to come **after** the product domain is designed (see §7).
- **Slices 5 + 6 + 7 shipped (2026-05-21):** vendor product CRUD with color variants, tiered USD pricing, image upload; admin moderation (approve/reject/takedown); USD→ZWG exchange rate admin; public `/api/v1/products` + `/api/v1/vendors/list` + product counts on `/api/v1/categories/list`; storefront `CategoryGrid`/`TopProducts`/`BestDeals`/`VendorGrid`/`Header` wired to real data; USD/ZWG header toggle backed by exchange rate; `/products` browse with category filter + search + pagination; `/products/[slug]` detail with image gallery, tier table, color picker, qty-aware unit price.
- **Next move:** Slice 8 — cart + checkout. Login-required cart, address capture, currency-aware totals, place order against a mobile-wallet payment instruction (admin manual confirm in v1).
