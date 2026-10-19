@php
    $ticker = config('site.ticker');
    // Filled and outlined names alternate. With an odd number of names that pattern would
    // break where the strip wraps round, so the names then run twice in each loop.
    $names = count($ticker) % 2 ? [...$ticker, ...$ticker] : $ticker;
@endphp
{{-- A large, slowly scrolling strip of service names between the hero and the work.
     Scrolling the page pushes it faster, and scrolling back up turns it around.
     Fast scrolling also leans the names into their direction of travel (resources/js/site/ticker.js). --}}
<section aria-label="What we do" class="overflow-hidden border-b border-line bg-paper py-[clamp(20px,2.5vw,40px)]">
    <div data-ticker class="flex w-max will-change-transform">
        @foreach ([0, 1] as $copy)
            <ul @if ($copy === 1) aria-hidden="true" @endif class="flex shrink-0 items-center">
                @foreach ($names as $i => $name)
                    {{-- Screen readers hear each name once, not the repeat --}}
                    <li
                        @if ($i >= count($ticker)) aria-hidden="true" @endif
                        class="flex items-center text-[clamp(40px,6vw,104px)] leading-none font-light tracking-[-0.02em] whitespace-nowrap"
                    >
                        {{-- Filled and outlined names alternate; hovering one swaps it --}}
                        <span @class([
                            'transition-colors duration-500 [-webkit-text-stroke:1px_var(--color-ink)]',
                            'text-transparent hover:text-ink' => $i % 2,
                            'hover:text-transparent' => ! ($i % 2),
                        ])>{{ $name }}</span>
                        <span aria-hidden="true" class="mx-[clamp(20px,2.5vw,44px)] flex gap-1.5">
                            <span class="size-[clamp(8px,0.8vw,12px)] rounded-full bg-cyan"></span>
                            <span class="size-[clamp(8px,0.8vw,12px)] rounded-full bg-magenta"></span>
                            <span class="size-[clamp(8px,0.8vw,12px)] rounded-full bg-yellow"></span>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
</section>
