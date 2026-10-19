@php
    $services = config('site.services');
@endphp
<section id="services" aria-labelledby="services-title" class="page-x section-y grid gap-14 lg:grid-cols-2 lg:gap-24">
    <div>
        <x-split-words
            as="h2"
            id="services-title"
            :text="$services['statement']"
            class="block max-w-[620px] text-[clamp(30px,3vw,48px)] leading-[1.1] font-light tracking-[-0.01em] text-balance"
        />
        <p data-reveal="up" style="--i: 3" class="mt-8 max-w-[560px] text-[17px] leading-relaxed text-slate">{{ $services['body'] }}</p>
    </div>

    <div class="lg:pt-2">
        <p data-reveal="up" class="text-[13px] font-medium tracking-[0.06em] uppercase">Services</p>
        <ul class="mt-6 border-b border-line">
            @foreach ($services['items'] as $i => $item)
                <li data-reveal="line" style="--i: {{ $i }}" class="group/row relative">
                    <span aria-hidden="true" class="draw-line absolute inset-x-0 top-0 h-px bg-line"></span>
                    {{-- The hovered or open row's rule turns ink, drawn from the left --}}
                    <span
                        aria-hidden="true"
                        class="absolute inset-x-0 top-0 h-px origin-left scale-x-0 bg-ink transition-transform duration-700 ease-(--ease-out-expo) group-hover/row:scale-x-100 group-has-[details[open]]/row:scale-x-100"
                    ></span>
                    <details class="line-text group">
                        <summary class="flex cursor-pointer items-center justify-between gap-6 py-4 text-[clamp(26px,2.4vw,38px)] leading-tight font-light">
                            <span class="transition-transform duration-500 ease-(--ease-out-expo) group-hover:translate-x-2 group-open:translate-x-2">{{ $item['name'] }}</span>
                            <span
                                aria-hidden="true"
                                class="relative size-5 shrink-0 transition-transform duration-500 ease-(--ease-out-expo) group-open:rotate-45"
                            >
                                <span class="absolute top-1/2 left-0 h-px w-full bg-ink"></span>
                                <span class="absolute top-0 left-1/2 h-full w-px bg-ink"></span>
                            </span>
                        </summary>
                        <ul class="grid gap-x-6 gap-y-2 pb-6 text-[16px] text-slate sm:grid-cols-2">
                            @foreach ($item['points'] as $j => $point)
                                <li class="point flex items-center gap-2.5" style="--j: {{ $j }}">
                                    <span aria-hidden="true" class="size-1 rounded-full bg-ink"></span>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </li>
            @endforeach
        </ul>
    </div>
</section>
