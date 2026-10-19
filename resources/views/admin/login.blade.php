<x-layouts.admin :title="'Sign in · '.config('site.brand.name')">
    <main class="grain relative isolate flex min-h-svh items-center justify-center overflow-clip bg-ink px-5 py-16 text-paper">
        {{-- The hero's drifting light, held still --}}
        <div aria-hidden="true" class="absolute inset-0 -z-10">
            <div class="absolute -top-1/3 -left-1/4 size-[80vmax] rounded-full bg-[radial-gradient(circle,#1d5f8f_0%,transparent_62%)] opacity-70 blur-2xl"></div>
            <div class="absolute -right-1/3 -bottom-1/2 size-[85vmax] rounded-full bg-[radial-gradient(circle,#2d6b5c_0%,transparent_60%)] opacity-60 blur-2xl"></div>
        </div>

        <div class="w-full max-w-[400px]">
            <a href="{{ route('home') }}" aria-label="{{ config('site.brand.name') }} website" class="text-[28px]"><x-logo-lockup /></a>
            <h1 class="mt-14 text-[clamp(32px,4vw,44px)] leading-[1.1] font-light tracking-[-0.01em]">
                Sign in to see your enquiries
            </h1>

            {{-- Shows "Signing in…" while it checks (resources/js/admin/login-form.js) --}}
            <form method="post" action="{{ route('admin.login.store') }}" data-login-form class="mt-10 flex flex-col gap-8">
                @csrf
                <div>
                    <label for="password" class="text-[14px] font-medium">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        autofocus
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        aria-describedby="password-error"
                        class="mt-2 block w-full border-0 border-b border-line-light bg-transparent px-0 py-2.5 text-[18px] transition-colors outline-none focus:border-paper aria-[invalid=true]:border-magenta"
                    >
                    <p id="password-error" role="status" aria-live="polite" class="mt-3 flex min-h-5 items-center gap-2 text-[14px]">
                        @error('password')
                            <span aria-hidden="true" class="size-2 shrink-0 rounded-full bg-magenta"></span>
                            {{ $message }}
                        @enderror
                    </p>
                </div>
                <button
                    type="submit"
                    class="group inline-flex items-center gap-3 self-start rounded-full bg-paper py-2 pr-2 pl-6 text-[16px] text-ink transition-colors hover:bg-mist disabled:cursor-wait disabled:opacity-80"
                >
                    <x-roll-label>Sign in</x-roll-label>
                    <x-arrow-circle class="size-8 group-hover:translate-x-0.5" />
                </button>
            </form>

            <a href="{{ route('home') }}" class="link-line mt-14 inline-block text-[14px] text-paper/70 hover:text-paper">Back to the website</a>
        </div>
    </main>
</x-layouts.admin>
