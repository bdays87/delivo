<template>
  <footer class="bg-neutral text-neutral-content">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 md:grid-cols-5">
      <div class="md:col-span-2">
        <NuxtLink to="/" class="flex items-center gap-2">
          <span class="grid h-10 w-10 place-items-center rounded-2xl bg-primary text-primary-content">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 7h11l2 4h5v6h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0V7z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          <span class="text-2xl font-extrabold tracking-tight">delivo<span class="text-primary">.</span></span>
        </NuxtLink>
        <p class="mt-4 max-w-sm text-sm opacity-70">
          Delivo is Zimbabwe's multi-vendor marketplace — local sellers, one cart, and door-to-door
          delivery anywhere in the country.
        </p>

        <div class="mt-6 flex gap-2">
          <a class="btn btn-circle btn-sm btn-ghost border-base-content/10" aria-label="X / Twitter">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22 5.92a8.2 8.2 0 0 1-2.36.65 4.1 4.1 0 0 0 1.8-2.27 8.2 8.2 0 0 1-2.6 1A4.1 4.1 0 0 0 11.85 9a11.64 11.64 0 0 1-8.45-4.3 4.1 4.1 0 0 0 1.27 5.47 4 4 0 0 1-1.86-.5v.05a4.1 4.1 0 0 0 3.3 4 4.1 4.1 0 0 1-1.85.07 4.1 4.1 0 0 0 3.83 2.85A8.24 8.24 0 0 1 2 18.43a11.6 11.6 0 0 0 6.29 1.84c7.55 0 11.68-6.26 11.68-11.68v-.53A8.3 8.3 0 0 0 22 5.92z" /></svg>
          </a>
          <a class="btn btn-circle btn-sm btn-ghost border-base-content/10" aria-label="Instagram">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5a4.25 4.25 0 0 0 4.25 4.25h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7zm5.5-2a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" /></svg>
          </a>
          <a class="btn btn-circle btn-sm btn-ghost border-base-content/10" aria-label="Facebook">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22.67 12.3a10.67 10.67 0 1 0-12.33 10.54v-7.46h-2.7v-3.08h2.7V9.85c0-2.67 1.58-4.14 4.01-4.14 1.17 0 2.39.21 2.39.21v2.62h-1.35c-1.32 0-1.73.82-1.73 1.66v2H15.6l-.47 3.08h-2.46v7.46a10.67 10.67 0 0 0 10-10.54z" /></svg>
          </a>
          <a class="btn btn-circle btn-sm btn-ghost border-base-content/10" aria-label="YouTube">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M23 6.18a3 3 0 0 0-2.12-2.12C19 3.5 12 3.5 12 3.5s-7 0-8.88.56A3 3 0 0 0 1 6.18 31.4 31.4 0 0 0 .5 12a31.4 31.4 0 0 0 .5 5.82 3 3 0 0 0 2.12 2.12C5 20.5 12 20.5 12 20.5s7 0 8.88-.56A3 3 0 0 0 23 17.82 31.4 31.4 0 0 0 23.5 12 31.4 31.4 0 0 0 23 6.18zM9.75 15.5v-7l6 3.5z" /></svg>
          </a>
        </div>
      </div>

      <div>
        <div class="mb-4 text-sm font-semibold opacity-60">Shop</div>
        <ul class="space-y-2 text-sm">
          <li><a class="opacity-80 hover:opacity-100">Groceries</a></li>
          <li><a class="opacity-80 hover:opacity-100">Fashion</a></li>
          <li><a class="opacity-80 hover:opacity-100">Electronics</a></li>
          <li><a class="opacity-80 hover:opacity-100">Home & Living</a></li>
          <li><a class="opacity-80 hover:opacity-100">All categories</a></li>
        </ul>
      </div>

      <div>
        <div class="mb-4 text-sm font-semibold opacity-60">Company</div>
        <ul class="space-y-2 text-sm">
          <li><a class="opacity-80 hover:opacity-100">About</a></li>
          <li><a class="opacity-80 hover:opacity-100">Careers</a></li>
          <li><a class="opacity-80 hover:opacity-100">Press</a></li>
          <li><a class="opacity-80 hover:opacity-100">Blog</a></li>
          <li><a class="opacity-80 hover:opacity-100">Sustainability</a></li>
        </ul>
      </div>

      <div>
        <div class="mb-4 text-sm font-semibold opacity-60">Sell on Delivo</div>
        <ul class="space-y-2 text-sm">
          <li><a class="opacity-80 hover:opacity-100">Become a seller</a></li>
          <li><a class="opacity-80 hover:opacity-100">Seller dashboard</a></li>
          <li><a class="opacity-80 hover:opacity-100">Fees & pricing</a></li>
          <li><a class="opacity-80 hover:opacity-100">Logistics</a></li>
          <li><a class="opacity-80 hover:opacity-100">Help centre</a></li>
        </ul>
      </div>
    </div>

    <div class="border-t border-base-content/10">
      <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-5 text-xs opacity-60 sm:flex-row">
        <p>© {{ new Date().getFullYear() }} Delivo. All rights reserved.</p>
        <div class="flex gap-5">
          <a class="hover:opacity-100">Terms</a>
          <a class="hover:opacity-100">Privacy</a>
          <a class="hover:opacity-100">Cookies</a>
          <a class="hover:opacity-100">Accessibility</a>
        </div>
      </div>
    </div>
  </footer>
</template>
