@php
    $brand = config('site.brand');
@endphp
{{-- overflow-clip, not overflow-hidden: hidden would make this a scroll container
     and the hero-exit scroll timeline would track it instead of the page. --}}
<section id="top" class="grain relative isolate flex min-h-svh items-center justify-center overflow-clip bg-ink text-paper">
    {{-- Slow drifting light in place of Bou's satellite video --}}
    <div aria-hidden="true" class="absolute inset-0 -z-10">
        <div class="blob-a absolute -top-1/4 -left-1/4 size-[80vmax] rounded-full bg-[radial-gradient(circle,#1d5f8f_0%,transparent_62%)] opacity-80 blur-2xl"></div>
        <div class="blob-b absolute -right-1/4 -bottom-1/3 size-[85vmax] rounded-full bg-[radial-gradient(circle,#2d6b5c_0%,transparent_60%)] opacity-70 blur-2xl"></div>
        <div class="blob-c absolute top-1/4 left-1/3 size-[45vmax] rounded-full bg-[radial-gradient(circle,#c7d9e6_0%,transparent_65%)] opacity-25 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_30%,#0a0a0a_95%)]"></div>
    </div>
    {{-- A soft light that eases toward the pointer while it moves over the hero (resources/js/motion/hero-glow.js) --}}
    <div
        aria-hidden="true"
        data-hero-glow
        class="pointer-events-none absolute inset-0 -z-10 opacity-0 transition-opacity duration-700"
        style="background: radial-gradient(520px circle at var(--gx, 50%) var(--gy, 45%), rgba(150, 200, 235, 0.16), transparent 65%)"
    ></div>

    <div class="hero-exit page-x flex max-w-[1080px] flex-col items-center pt-24 pb-16 text-center">
        <x-split-words
            as="h1"
            mode="load"
            :text="config('site.hero.headline')"
            class="text-[clamp(38px,4.6vw,72px)] leading-[1.1] font-light tracking-[-0.01em] text-balance"
        />

        <div
            class="rise mt-10 flex flex-wrap items-center justify-center gap-2 rounded-[22px] border border-line-light p-1.5 text-[13px] backdrop-blur-sm sm:rounded-full"
            style="animation-delay: 900ms"
        >
            @foreach ($brand['locations'] as $i => $place)
                <span class="pop inline-flex items-center gap-1.5 rounded-full bg-paper/10 px-3 py-1.5" style="animation-delay: {{ 1050 + $i * 110 }}ms">
                    <svg aria-hidden="true" viewBox="0 0 16 16" class="size-3" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M8 14s5-4.5 5-8.2A5 5 0 0 0 3 5.8C3 9.5 8 14 8 14Z" />
                        <circle cx="8" cy="6" r="1.8" />
                    </svg>
                    {{ $place }}
                </span>
            @endforeach
            <span class="px-3 py-1.5">
                <a href="mailto:{{ $brand['email'] }}" class="link-line">{{ $brand['email'] }}</a>
            </span>
        </div>
    </div>

    <a
        href="#work"
        class="rise group absolute bottom-8 left-1/2 flex -translate-x-1/2 flex-col items-center gap-3 text-[13px] text-paper/70 hover:text-paper"
        style="animation-delay: 1200ms"
    >
        Scroll to see our work
        {{-- A dot travelling down a short track --}}
        <span aria-hidden="true" class="relative h-10 w-px overflow-hidden bg-paper/20">
            <span class="absolute inset-x-0 top-0 h-3 bg-paper motion-safe:animate-[scroll-cue_1.8s_var(--ease-bou)_infinite]"></span>
        </span>
    </a>
</section>
