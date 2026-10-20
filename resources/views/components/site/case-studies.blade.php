@php
    $studies = array_values(array_filter(config('site.case_studies'), fn ($study) => $study['featured'] ?? false));
    $pad = fn (int $n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
@endphp
<section id="case-studies" aria-labelledby="cases-title" class="page-x pb-[clamp(90px,8vw,160px)]">
    <div class="mb-[clamp(24px,3vw,44px)] flex items-end justify-between gap-6">
        <x-split-words
            as="h2"
            id="cases-title"
            text="Case studies"
            class="font-display text-[clamp(28px,3vw,48px)] leading-none font-light whitespace-nowrap"
        />
        <p class="text-[15px] text-slate">{{ count($studies) }} projects</p>
    </div>

    {{-- The case studies as a stack of cards: each one holds near the top of the screen
         while the next slides up over it, and the one underneath shrinks back and fades
         (resources/js/site/case-studies.js) --}}
    <ol data-case-stack class="grid gap-[clamp(20px,2.5vw,36px)]">
        @foreach ($studies as $i => $study)
            <li id="case-{{ $study['id'] }}" class="sticky top-[88px]">
                <article
                    aria-labelledby="case-{{ $study['id'] }}-title"
                    class="case-card grid gap-[clamp(20px,3vw,48px)] rounded-[clamp(20px,2vw,32px)] bg-paper p-[clamp(18px,2.6vw,44px)] shadow-[0_-24px_60px_-36px_rgb(10_10_10/0.3)] lg:grid-cols-[minmax(0,6fr)_minmax(0,5fr)]"
                >
                    <div class="case-card-fade flex flex-col">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <span class="text-[14px] text-slate tabular-nums">{{ $pad($i + 1) }} / {{ $pad(count($studies)) }}</span>
                            <ul class="flex flex-wrap gap-2" aria-label="Services">
                                @foreach ($study['tags'] as $tag)
                                    <x-tag>{{ $tag }}</x-tag>
                                @endforeach
                            </ul>
                        </div>
                        <p class="mt-6 text-[15px] font-semibold">{{ $study['client'] }}</p>
                        <h3
                            id="case-{{ $study['id'] }}-title"
                            class="mt-1.5 font-display text-[clamp(28px,2.8vw,48px)] leading-[1.06] font-light tracking-[-0.02em] text-balance"
                        >{{ $study['summary'] }}</h3>
                        <p class="mt-4 max-w-[560px] text-[16px] leading-relaxed text-slate">{{ $study['description'] }}</p>

                        {{-- Three columns; on a phone, one row each, with the figure beside its label --}}
                        <dl class="mt-7 grid border-t border-line sm:grid-cols-3">
                            @foreach ($study['facts'] as $fact)
                                <div class="border-b border-line py-3 last:border-b-0 sm:border-r sm:border-b-0 sm:pt-4 sm:pr-3 sm:pb-0 sm:last:border-r-0 sm:[&:not(:first-child)]:pl-4">
                                    <dt class="sr-only">{{ $fact['label'] }}</dt>
                                    <dd class="flex items-baseline justify-between gap-4 sm:block">
                                        <span class="block shrink-0 font-display text-[clamp(22px,2.2vw,34px)] leading-none font-light tracking-[-0.02em]"><x-count-up :value="$fact['value']" /></span>
                                        <span class="block text-right text-[14px] leading-snug text-slate sm:mt-2 sm:text-left">{{ $fact['label'] }}</span>
                                    </dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="mt-7 flex flex-wrap items-center gap-x-7 gap-y-4 lg:mt-auto lg:pt-7">
                            <a
                                href="#contact"
                                data-magnetic
                                class="group inline-flex items-center gap-3 rounded-full bg-ink py-2 pr-2 pl-5 text-[15px] text-paper transition-colors hover:bg-slate"
                            >
                                <x-roll-label>Start a similar project</x-roll-label>
                                <x-arrow-circle inverted class="size-7 group-hover:translate-x-0.5" />
                            </a>
                            <x-text-link :href="$study['url']" external>Visit website</x-text-link>
                        </div>
                    </div>

                    {{-- The site fills the card's height beside the text; hovering scrolls down its page --}}
                    <a
                        href="{{ $study['url'] }}"
                        target="_blank"
                        rel="noopener"
                        tabindex="-1"
                        aria-hidden="true"
                        class="group/cover case-card-fade relative block aspect-[4/3] overflow-hidden rounded-[clamp(14px,1.4vw,22px)] lg:aspect-auto lg:min-h-[440px]"
                    >
                        <x-case-cover :study="$study" size="feature" />
                    </a>
                </article>
            </li>
        @endforeach
    </ol>
</section>
