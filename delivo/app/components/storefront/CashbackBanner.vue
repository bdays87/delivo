<template>
  <section class="mx-auto max-w-7xl px-4 py-16">
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-primary via-primary to-accent p-8 text-primary-content md:p-12">
      <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-base-100/10 blur-2xl"></div>
      <div class="absolute -bottom-16 -left-10 h-56 w-56 rounded-full bg-base-100/10 blur-2xl"></div>

      <div class="relative grid items-center gap-8 md:grid-cols-2">
        <div>
          <div class="badge bg-base-100/20 text-primary-content font-semibold backdrop-blur">Loyalty rewards</div>
          <h3 class="mt-4 text-3xl font-extrabold md:text-4xl">
            Earn cashback on every order over $200.
          </h3>
          <p class="mt-3 max-w-md opacity-90">
            Join Delivo+ for free delivery, priority support, and instant cashback paid straight to
            your mobile money wallet. Cancel any time.
          </p>
          <div class="mt-6 flex flex-wrap gap-3">
            <a class="btn rounded-full border-none bg-base-100 text-primary hover:bg-base-100/90">
              Join Delivo+
            </a>
            <a class="btn btn-ghost rounded-full text-primary-content hover:bg-base-100/15">
              Learn more →
            </a>
          </div>
        </div>

        <div class="relative h-full min-h-[220px]">
          <div class="absolute right-0 top-0 hidden h-full w-full md:block">
            <div class="absolute right-4 top-2 rotate-3 rounded-3xl bg-base-100/15 p-5 shadow-xl backdrop-blur">
              <div class="text-xs opacity-80">Cashback earned</div>
              <div class="mt-1 text-3xl font-bold">$32.40</div>
              <div class="mt-1 text-xs opacity-80">This month</div>
            </div>
            <div class="absolute right-12 bottom-2 -rotate-6 rounded-3xl bg-base-100 p-5 text-base-content shadow-xl">
              <div class="text-xs opacity-60">Delivo+ member</div>
              <div class="mt-1 text-lg font-bold">Harare · Active</div>
              <div class="mt-2 text-xs opacity-60">Free delivery · Priority support</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
