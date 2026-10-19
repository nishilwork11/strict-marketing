<x-layouts.admin>
    <x-admin.bar />
    <main class="mx-auto max-w-[1440px] px-4 pt-8 pb-16 sm:px-6 sm:pt-10 lg:px-8">
        <h1 class="text-[clamp(32px,3.4vw,48px)] leading-[1.1] font-light tracking-[-0.01em]">Enquiries</h1>
        <p class="mt-2 text-[16px] text-slate">Everything sent through the contact form on your website.</p>

        @if ($databaseError ?? false)
            <x-admin.database-notice />
        @elseif ($enquiries->isNotEmpty())
            <x-admin.stat-tiles :enquiries="$enquiries" :now="$now" />
            <x-admin.inbox :enquiries="$enquiries" :now="$now" :filter="$filter" :query="$query" />
        @else
            <x-admin.stat-tiles :enquiries="$enquiries" :now="$now" />
            <div class="mt-8 flex flex-col items-center rounded-2xl bg-paper px-6 py-16 text-center">
                <span aria-hidden="true" class="flex gap-1.5">
                    <span class="size-2.5 rounded-full bg-cyan"></span>
                    <span class="size-2.5 rounded-full bg-magenta"></span>
                    <span class="size-2.5 rounded-full bg-yellow"></span>
                </span>
                <h2 class="mt-6 text-[28px] leading-tight font-light">No enquiries yet</h2>
                <p class="mt-2 max-w-[420px] text-[16px] text-slate">
                    When someone sends the contact form on your website, their enquiry appears here.
                </p>
                <a href="{{ route('home') }}#contact" class="link-line mt-6 text-[15px]">See the contact form</a>
            </div>
        @endif
    </main>
</x-layouts.admin>
