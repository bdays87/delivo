<template>
  <section class="mx-auto max-w-7xl px-4 pb-16">
    <div class="grid items-center gap-8 rounded-[2.5rem] bg-neutral p-8 text-neutral-content md:grid-cols-2 md:p-12">
      <div>
        <h3 class="text-3xl font-extrabold md:text-4xl">
          Get $10 off your first order.
        </h3>
        <p class="mt-3 max-w-md opacity-80">
          Subscribe and we'll send you a welcome code plus weekly drops from new vendors near you.
        </p>
      </div>
      <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent>
        <input
          type="email"
          placeholder="you@email.com"
          class="input input-lg flex-1 rounded-full border-none bg-base-100 text-base-content"
        />
        <button class="btn btn-primary btn-lg rounded-full px-7" type="submit">
          Subscribe
        </button>
      </form>
    </div>
  </section>
</template>
