@php
    $brand = config('site.brand');
@endphp
<footer class="px-3 pb-3 sm:px-5 sm:pb-5">
    <div data-reveal="up" class="rounded-2xl bg-mist p-3 sm:p-5">
        <div class="flex flex-col gap-8 px-3 pt-8 pb-12 sm:flex-row sm:items-end sm:justify-between sm:px-6 lg:px-10 lg:pt-12">
            <div class="@container sm:flex-1">
                {{-- Letters rise one after another; each keeps a little side room so the
                     tight tracking doesn't clip its edges. The size only drops below its
                     usual scale when the word (about 0.6em per capital) wouldn't fit. --}}
                <p
                    data-reveal="words"
                    class="text-[length:min(clamp(60px,10vw,168px),100cqi/(var(--letters)*0.6))] leading-none font-bold tracking-tight"
                    style="--letters: {{ mb_strlen($brand['wordmark']) }}"
                ><span class="sr-only">{{ $brand['wordmark'] }}</span>@foreach (mb_str_split($brand['wordmark']) as $i => $letter)<span aria-hidden="true" class="word -mx-[0.04em] px-[0.04em]"><span class="word-inner" style="--i: {{ $i }}">{{ $letter }}</span></span>@endforeach</p>
                {{-- 14px, or smaller when its letters (about 0.9em each, with the gaps) wouldn't fit --}}
                <x-strapline class="mt-3 gap-x-[0.6em] text-[length:min(14px,100cqi/(var(--chars)*0.9))] font-semibold tracking-[0.1em]" />
                <p class="mt-4 max-w-[460px] text-[17px] text-slate">{{ $brand['tagline'] }}</p>
            </div>
            <a href="#top" data-magnetic class="group inline-flex items-center gap-2.5 text-[15px]">
                <x-roll-label>Back to top</x-roll-label>
                <x-arrow-circle class="-rotate-90 group-hover:-translate-y-0.5" />
            </a>
        </div>

        <div class="grid gap-6 rounded-xl bg-ink px-6 py-6 text-[14px] text-paper sm:grid-cols-2 lg:px-8">
            <div>
                <p class="font-semibold">Contact Email</p>
                <a href="mailto:{{ $brand['email'] }}" class="link-line">{{ $brand['email'] }}</a>
            </div>
            <div class="sm:text-right">
                <p>© {{ $brand['year'] }} {{ $brand['name'] }}</p>
                <p class="text-paper/60">All rights reserved</p>
            </div>
        </div>
    </div>
</footer>
