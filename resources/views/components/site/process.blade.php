@php
    $process = config('site.process');
@endphp
<section id="process" aria-labelledby="process-title" class="page-x section-y">
    <x-split-words
        as="h2"
        id="process-title"
        :text="$process['title']"
        class="block font-display text-[clamp(30px,3vw,48px)] leading-none font-light"
    />
    {{-- Each step's marker pops in and its rule draws across the grey track in turn,
         then its text rises --}}
    <ol class="mt-12 grid gap-x-[clamp(14px,1.56vw,30px)] gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($process['steps'] as $i => $step)
            <li data-reveal="line" style="--i: {{ $i }}" class="relative pt-6">
                <span aria-hidden="true" class="absolute inset-x-0 top-0 h-px bg-line"></span>
                <span aria-hidden="true" class="draw-line absolute inset-x-0 top-0 h-px bg-ink"></span>
                <span aria-hidden="true" class="line-dot absolute -top-[5px] left-0 size-[11px] rounded-full border-2 border-paper bg-ink"></span>
                <div class="line-text">
                    <div class="flex items-baseline justify-between">
                        <p class="font-display text-[clamp(24px,2vw,30px)] font-light">{{ $step['title'] }}</p>
                        <span class="text-[14px] text-grey tabular-nums">{{ $i + 1 }}</span>
                    </div>
                    <p class="mt-3 max-w-[300px] text-[16px] leading-relaxed text-slate">{{ $step['text'] }}</p>
                </div>
            </li>
        @endforeach
    </ol>
</section>
