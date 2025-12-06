<div class="flex w-screen h-screen justify-center items-center ">
    <div class="card lg:card-side bg-base-100 shadow-xl rounded-lg mx-auto border border-slate-300">
      <figure class="shadow-xl">
        <img
          src="{{ asset('assets/image/login-side.jpeg') }}"
          alt="Album"
          class="size-90"/>
      </figure>

      <div class="card-body w-md">
        <div class="flex items-center">
            <x-partials.app-logo class="size-12"/>
            <h2 class="card-title text-2xl text-primary font-bold">Simlab</h2>
        </div>

        <div>
            <h2 class="card-title text-xl text-primary font-bold">Login</h2>
        </div>

        <form action="" class="" wire:submit="login">
            <div class="flex flex-col gap-4 mb-8">
                <x-form.input name="username" label="Username"/>

                <x-form.input name="password" type="password" label="Password"/>
            </div>

            <div class="card-actions ">
                <x-button type="submit" class="w-full" target="login">Login</x-button>
              {{-- <button class="btn btn-primary w-full">Login</button> --}}
            </div>
        </form>
      </div>
    </div>
</div>
