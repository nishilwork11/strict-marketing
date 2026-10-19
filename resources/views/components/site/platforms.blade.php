@php
    $platforms = config('site.platforms');
@endphp
<section aria-labelledby="platforms-title" class="page-x pb-[clamp(90px,8vw,160px)]">
    <x-split-words
        as="h2"
        id="platforms-title"
        :text="$platforms['statement']"
        class="mx-auto block max-w-[760px] text-center text-[clamp(30px,3.4vw,52px)] leading-[1.12] font-light tracking-[-0.01em] text-balance"
    />
    {{-- Every cell draws a right border; the list overhangs its clipping box by 1px,
         so the border on each row's last cell is hidden and only dividers remain. --}}
    <div class="mt-[clamp(48px,5vw,90px)] overflow-hidden">
        <ul class="-mr-px grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($platforms['items'] as $i => $name)
                <li
                    data-reveal="mask"
                    style="--i: {{ $i }}"
                    class="group relative flex aspect-[3/2] items-center justify-center overflow-hidden border-r border-b border-line px-3 text-center text-[clamp(17px,1.4vw,21px)] font-medium tracking-tight lg:aspect-[5/4]"
                >
                    {{-- Fills from the bottom on hover --}}
                    <span
                        aria-hidden="true"
                        class="absolute inset-0 origin-bottom scale-y-0 bg-ink transition-transform duration-500 ease-(--ease-out-expo) group-hover:scale-y-100"
                    ></span>
                    {{-- The name slides up into its cell on reveal. On hover it rolls up and
                         a white copy follows it in from below, riding the fill. --}}
                    <span class="relative block overflow-hidden">
                        <span class="mask-in relative block">
                            <span class="block transition-transform duration-500 ease-(--ease-out-expo) group-hover:-translate-y-full">{{ $name }}</span>
                            <span
                                aria-hidden="true"
                                class="absolute inset-0 block translate-y-full text-paper transition-transform duration-500 ease-(--ease-out-expo) group-hover:translate-y-0"
                            >{{ $name }}</span>
                        </span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
