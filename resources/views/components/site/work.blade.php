@php
    $intro = config('site.intro');
    $studies = config('site.case_studies');
@endphp
<section class="page-x section-y flex flex-col items-center text-center">
    <x-split-words
        as="p"
        mode="scrub"
        :text="$intro['statement']"
        class="max-w-[880px] font-display text-[clamp(30px,3.4vw,52px)] leading-[1.12] font-light tracking-[-0.01em] text-balance"
    />
    <div class="mt-10" data-reveal="up">
        <x-text-link :href="$intro['cta']['href']">{{ $intro['cta']['label'] }}</x-text-link>
    </div>
</section>

<section id="work" aria-labelledby="work-title" class="page-x pb-[clamp(60px,6vw,120px)]">
    <h2 id="work-title" class="sr-only">Selected work</h2>
    <ul class="grid gap-x-[clamp(14px,1.56vw,30px)] gap-y-[clamp(36px,3vw,56px)] sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($studies as $i => $study)
            @php
                $featured = $study['featured'] ?? false;
            @endphp
            <li data-reveal="unmask" style="--i: {{ $i % 3 }}">
                {{-- A featured project opens its case study below; the rest open their site --}}
                <a
                    href="{{ $featured ? "#case-{$study['id']}" : $study['url'] }}"
                    @unless ($featured) target="_blank" rel="noopener" @endunless
                    class="group block"
                >
                    <div class="unmask-frame relative aspect-[16/9] overflow-hidden rounded">
                        <div class="unmask-zoom h-full">
                            <div class="h-full transition-transform duration-1000 ease-(--ease-bou) group-hover:scale-[1.04]">
                                <x-case-cover :study="$study" />
                            </div>
                        </div>
                        {{-- Slides up on hover --}}
                        <span class="absolute top-4 right-4 z-20 inline-flex translate-y-2 items-center gap-2 rounded-full bg-paper py-1.5 pr-1.5 pl-3.5 text-[13px] text-ink opacity-0 shadow-[0_6px_20px_-8px_rgb(10_10_10/0.4)] transition-[opacity,translate] duration-700 ease-(--ease-out-expo) group-hover:translate-y-0 group-hover:opacity-100 group-focus-visible:translate-y-0 group-focus-visible:opacity-100">
                            {{ $featured ? 'View case study' : 'Visit website' }}
                            <x-arrow-circle @class(['-rotate-45' => ! $featured]) />
                        </span>
                    </div>
                    <h3 class="unmask-text mt-4 text-[22px] leading-snug font-semibold">
                        <span class="bg-[linear-gradient(currentColor,currentColor)] bg-[length:0%_1px] bg-left-bottom bg-no-repeat transition-[background-size] duration-700 ease-(--ease-out-expo) group-hover:bg-[length:100%_1px]">{{ $study['client'] }}</span>
                        @unless ($featured)
                            <span class="sr-only">(opens in a new tab)</span>
                        @endunless
                    </h3>
                    <p class="unmask-text mt-0.5 text-[17px] leading-snug font-light text-slate">{{ $study['summary'] }}</p>
                </a>
                <ul class="unmask-text mt-3 flex flex-wrap gap-2" aria-label="Services">
                    @foreach ($study['tags'] as $tag)
                        <x-tag>{{ $tag }}</x-tag>
                    @endforeach
                </ul>
            </li>
        @endforeach

        {{-- Rounds off the grid: the next project could be yours. Spans both columns where there are two. --}}
        <li data-reveal="unmask" style="--i: {{ count($studies) % 3 }}" class="sm:col-span-2 lg:col-span-1">
            <a href="#contact" class="group block">
                <div class="unmask-frame relative aspect-[16/9] overflow-hidden rounded sm:aspect-[32/9] lg:aspect-[16/9]">
                    <div class="unmask-zoom h-full">
                        <div class="h-full transition-transform duration-1000 ease-(--ease-bou) group-hover:scale-[1.04]">
                            <div class="grain relative isolate flex h-full overflow-hidden rounded bg-ink text-paper">
                                <div aria-hidden="true" class="absolute -top-1/2 -left-1/4 aspect-square w-[80%] rounded-full bg-[radial-gradient(circle,#1d5f8f_0%,transparent_62%)] opacity-80 transition-transform duration-1400 ease-(--ease-out-expo) group-hover:translate-x-[10%]"></div>
                                <div aria-hidden="true" class="absolute -right-[12%] -bottom-[38%] aspect-square w-[70%] rounded-full border border-paper/20 transition-transform duration-1400 ease-(--ease-out-expo) group-hover:-translate-x-[14%] group-hover:-translate-y-[6%]"></div>
                                <p class="relative mt-auto p-[6%] font-display text-[clamp(30px,3vw,44px)] leading-[1.02] font-light tracking-[-0.02em]">Your project next?</p>
                            </div>
                        </div>
                    </div>
                    <span class="absolute top-4 right-4 z-20 inline-flex translate-y-2 items-center gap-2 rounded-full bg-paper py-1.5 pr-1.5 pl-3.5 text-[13px] text-ink opacity-0 transition-[opacity,translate] duration-700 ease-(--ease-out-expo) group-hover:translate-y-0 group-hover:opacity-100 group-focus-visible:translate-y-0 group-focus-visible:opacity-100">
                        Start a project
                        <x-arrow-circle />
                    </span>
                </div>
                <h3 class="unmask-text mt-4 text-[22px] leading-snug font-semibold">
                    <span class="bg-[linear-gradient(currentColor,currentColor)] bg-[length:0%_1px] bg-left-bottom bg-no-repeat transition-[background-size] duration-700 ease-(--ease-out-expo) group-hover:bg-[length:100%_1px]">Let's work together</span>
                </h3>
                <p class="unmask-text mt-0.5 text-[17px] leading-snug font-light text-slate">Websites, marketing, software and print, from one team.</p>
            </a>
        </li>
    </ul>
</section>
