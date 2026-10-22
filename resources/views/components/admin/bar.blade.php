<header class="bg-ink text-paper">
    <div class="mx-auto flex h-[72px] max-w-[1440px] items-center justify-between gap-4 px-4 sm:h-20 sm:px-6 lg:px-8">
        <div class="flex items-center gap-5">
            <a href="{{ route('admin.enquiries.index') }}" aria-label="Enquiries" class="flex text-[clamp(24px,7.5vw,30px)]"><x-logo-lockup /></a>
            <span aria-hidden="true" class="hidden h-9 w-px bg-paper/25 sm:block"></span>
            <span class="hidden text-[14px] text-paper/70 sm:block">Admin</span>
        </div>
        <div class="flex items-center gap-6 text-[15px]">
            <a
                href="{{ route('admin.email') }}"
                class="link-line hidden opacity-90 hover:opacity-100 sm:inline"
                @if (request()->routeIs('admin.email')) aria-current="page" @endif
            >Email</a>
            <a href="{{ route('home') }}" class="link-line hidden opacity-90 hover:opacity-100 sm:inline">View website</a>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button
                    type="submit"
                    class="rounded-full border border-paper/40 px-4 py-1.5 transition-colors hover:border-paper hover:bg-paper hover:text-ink"
                >Sign out</button>
            </form>
        </div>
    </div>
</header>
