@php
    $print = config('site.print');
    $corner = 'absolute size-6 border-paper/30';
@endphp
<section id="print" aria-labelledby="print-title" class="page-x">
    {{-- panel-in: grows to full size as it scrolls into view --}}
    <div class="panel-in grain relative isolate overflow-hidden rounded bg-ink py-[clamp(70px,7vw,130px)] text-paper">
        <div
            aria-hidden="true"
            class="blob-b absolute -inset-1/4 -z-10 bg-[radial-gradient(40%_35%_at_25%_20%,#0b3a52_0%,transparent_70%),radial-gradient(35%_40%_at_80%_80%,#3a1630_0%,transparent_70%)]"
        ></div>

        {{-- Crop marks in the panel corners, as on a press sheet. They grow out of each corner on reveal. --}}
        <div aria-hidden="true" data-reveal="crop" class="pointer-events-none absolute inset-4 sm:inset-6">
            <span class="{{ $corner }} top-0 left-0 origin-top-left border-t border-l"></span>
            <span class="{{ $corner }} top-0 right-0 origin-top-right border-t border-r"></span>
            <span class="{{ $corner }} bottom-0 left-0 origin-bottom-left border-b border-l"></span>
            <span class="{{ $corner }} right-0 bottom-0 origin-bottom-right border-r border-b"></span>
        </div>

        <div class="mx-auto max-w-[860px] px-6 text-center">
            <p data-reveal="up" class="flex items-center justify-center gap-3 text-[13px] font-medium tracking-[0.06em] uppercase">
                <span aria-hidden="true" class="flex gap-1">
                    <span class="ink-dot size-2 rounded-full bg-cyan"></span>
                    <span class="ink-dot size-2 rounded-full bg-magenta" style="--d: 1"></span>
                    <span class="ink-dot size-2 rounded-full bg-yellow" style="--d: 2"></span>
                    <span class="ink-dot size-2 rounded-full border border-paper/60 bg-ink" style="--d: 3"></span>
                </span>
                {{ $print['label'] }}
            </p>
            <x-split-words
                as="h2"
                id="print-title"
                :text="$print['headline']"
                class="misregister mt-6 block text-[clamp(32px,3.6vw,56px)] leading-[1.1] font-light text-balance"
            />
            <p data-reveal="up" style="--i: 2" class="mx-auto mt-6 max-w-[620px] text-[17px] leading-relaxed text-paper/75">{{ $print['body'] }}</p>
            <div data-reveal="up" style="--i: 3">
                <a
                    href="{{ $print['cta']['href'] }}"
                    data-magnetic
                    class="group mt-9 inline-flex items-center gap-3 rounded-full bg-paper py-2 pr-2 pl-5 text-[15px] text-ink transition-colors hover:bg-mist"
                >
                    <x-roll-label>{{ $print['cta']['label'] }}</x-roll-label>
                    <x-arrow-circle class="size-7 group-hover:translate-x-0.5" />
                </a>
            </div>
        </div>

        <ol class="mx-auto mt-[clamp(56px,5vw,90px)] grid max-w-[1100px] gap-8 px-6 sm:grid-cols-3 sm:px-10">
            @foreach ($print['steps'] as $i => $step)
                <li data-reveal="line" style="--i: {{ $i }}" class="relative pt-5">
                    <span aria-hidden="true" class="draw-line absolute inset-x-0 top-0 h-px bg-line-light"></span>
                    <div class="line-text">
                        <span class="text-[14px] text-paper/50 tabular-nums">{{ $i + 1 }}</span>
                        <p class="mt-2 text-[20px] font-medium">{{ $step['title'] }}</p>
                        <p class="mt-1.5 text-[15px] leading-relaxed text-paper/65">{{ $step['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="marquee-track mt-[clamp(56px,5vw,90px)] flex flex-col gap-3 overflow-hidden py-1">
            <p class="sr-only">Printing products we offer</p>
            @foreach ($print['rows'] as $r => $row)
                {{-- Two identical copies side by side; the strip slides by half its width and loops. --}}
                <div @class(['flex w-max', 'marquee' => $r === 0, 'marquee-reverse' => $r !== 0])>
                    @foreach ([0, 1] as $copy)
                        <ul @if ($copy === 1) aria-hidden="true" @endif class="flex shrink-0 gap-3 pr-3">
                            @foreach ($row as $product)
                                <li class="w-[260px] shrink-0 rounded border border-line-light bg-paper/[0.04] p-5 backdrop-blur-sm transition-[background-color,border-color,translate] duration-500 ease-(--ease-out-expo) hover:-translate-y-1 hover:border-paper/60 hover:bg-paper/10 sm:w-[280px]">
                                    <p class="text-[22px] leading-tight font-semibold">{{ $product['name'] }}</p>
                                    <p class="mt-2 truncate text-[15px] text-paper/60">{{ $product['detail'] }}</p>
                                    <p class="mt-4 flex items-center gap-2 text-[14px] text-paper/80">
                                        <svg aria-hidden="true" viewBox="0 0 16 16" class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.4">
                                            <rect x="2.5" y="2.5" width="11" height="11" rx="1" />
                                            <path d="M2.5 6.5h11M6.5 2.5v11" />
                                        </svg>
                                        {{ $product['size'] }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>
