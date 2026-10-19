{{-- Cover art for a project: the site itself, in a browser window on the project's
     colours. On a card it rises out of the bottom edge and lifts when a parent .group
     is hovered; on a case study it fills the cover and, when a parent .group/cover is
     hovered, scrolls down the page. Screenshots live in public/images/work/. --}}
@props(['study', 'size' => 'card'])
@php
    $cover = $study['cover'];
    $feature = $size === 'feature';
    $host = preg_replace('/^www\./', '', parse_url($study['url'], PHP_URL_HOST));
@endphp
<div @class(['grain relative isolate h-full w-full overflow-hidden rounded', 'text-paper' => $cover['dark'], 'text-ink' => ! $cover['dark']]) style="background: linear-gradient(140deg, {{ $cover['from'] }} 0%, {{ $cover['to'] }} 100%)">
    {{-- Large soft ring for depth; drifts when a parent .group is hovered --}}
    <div aria-hidden="true" class="absolute -right-[20%] -bottom-[45%] aspect-square w-[85%] rounded-full opacity-40 transition-transform duration-1400 ease-(--ease-out-expo) group-hover:-translate-x-[8%] group-hover:scale-110" style="background: radial-gradient(circle, {{ $cover['to'] }} 0%, transparent 70%)"></div>
    <div aria-hidden="true" @class([
        'absolute -right-[12%] -bottom-[38%] aspect-square w-[70%] rounded-full border transition-transform duration-1400 ease-(--ease-out-expo) group-hover:-translate-x-[14%] group-hover:-translate-y-[6%]',
        'border-paper/20' => $cover['dark'],
        'border-ink/15' => ! $cover['dark'],
    ])></div>

    {{-- The site in a browser window, above the grain --}}
    <div @class([
        'absolute z-10 flex flex-col overflow-hidden rounded-t-[clamp(6px,0.7vw,10px)] bg-paper shadow-[0_30px_70px_-24px_rgb(0_0_0/0.55)]',
        'inset-x-[9%] top-[13%] -bottom-[20%] transition-transform duration-1000 ease-(--ease-bou) group-hover:-translate-y-[5%]' => ! $feature,
        'inset-x-[7%] top-[9%] bottom-0' => $feature,
    ])>
        <div aria-hidden="true" class="flex h-[clamp(14px,1.5vw,24px)] shrink-0 items-center gap-[clamp(3px,0.3vw,5px)] border-b border-ink/10 bg-mist px-[clamp(6px,0.9vw,12px)]">
            <span class="size-[clamp(4px,0.45vw,7px)] rounded-full bg-cyan"></span>
            <span class="size-[clamp(4px,0.45vw,7px)] rounded-full bg-magenta"></span>
            <span class="size-[clamp(4px,0.45vw,7px)] rounded-full bg-yellow"></span>
            <span class="mx-auto truncate rounded-full bg-paper px-[1.2em] py-[0.15em] text-[clamp(7px,0.65vw,11px)] leading-none text-slate">{{ $host }}</span>
        </div>
        @if ($feature)
            <img
                src="{{ asset("images/work/{$study['id']}-full.webp") }}"
                alt="The {{ $study['client'] }} website"
                loading="lazy"
                decoding="async"
                class="block min-h-0 w-full flex-1 object-cover object-top motion-safe:transition-[object-position] motion-safe:duration-[7s] motion-safe:ease-in-out motion-safe:group-hover/cover:object-bottom"
            >
        @else
            <img
                src="{{ asset("images/work/{$study['id']}.webp") }}"
                alt="The {{ $study['client'] }} website"
                width="1080"
                height="675"
                loading="lazy"
                decoding="async"
                class="block w-full"
            >
        @endif
    </div>
</div>
