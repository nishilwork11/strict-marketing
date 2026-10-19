{{-- Transparent over the hero, a white card after scrolling (resources/js/site/header.js) --}}
@php
    $nav = config('site.nav');
@endphp
<header data-site-header class="rise fixed inset-x-0 top-0 z-50 transition-[translate] duration-500 ease-(--ease-out-expo)" style="animation-delay: 600ms">
    <div data-header-card class="relative mx-auto overflow-hidden transition-[background-color,border-color,margin,border-radius] duration-500 ease-(--ease-bou) border border-transparent text-paper">
        {{-- Reading progress along the bottom edge of the header card --}}
        <span aria-hidden="true" data-header-progress class="scroll-progress absolute inset-x-0 bottom-0 h-[2px] origin-left bg-ink transition-opacity opacity-0"></span>
        <div class="page-x flex h-[72px] items-center justify-between gap-6 sm:h-20">
            {{-- 36px puts each of the mark's nine grid steps on a whole 4px, so its edges stay crisp --}}
            <a href="#top" class="flex" aria-label="{{ config('site.brand.name') }} home">
                <x-logo-mark class="size-9" />
            </a>

            <nav aria-label="Main" class="hidden md:block">
                <ul class="flex items-center gap-9 text-[15px]">
                    @foreach ($nav as $item)
                        <li>
                            <a href="{{ $item['href'] }}" data-nav-link class="link-line opacity-90 hover:opacity-100 aria-[current]:opacity-100">{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <a href="#contact" data-magnetic class="group hidden items-center gap-2.5 text-[15px] md:inline-flex">
                <x-roll-label>Let's talk</x-roll-label>
                <x-arrow-circle inverted data-header-arrow class="size-7 group-hover:translate-x-1" />
            </a>

            <button type="button" data-menu-button class="inline-flex items-center gap-2 text-[15px] md:hidden" aria-expanded="false" aria-controls="mobile-menu">
                <span data-menu-label>Menu</span>
                <span aria-hidden="true" class="flex w-5 flex-col gap-[5px]">
                    <span data-menu-line="top" class="h-px w-full bg-current transition-transform"></span>
                    <span data-menu-line="bottom" class="h-px w-full bg-current transition-transform"></span>
                </span>
            </button>
        </div>

        <nav id="mobile-menu" aria-label="Mobile" class="page-x pb-8 md:hidden" hidden>
            <ul class="flex flex-col border-t border-line">
                @foreach ([...$nav, ['label' => 'Contact', 'href' => '#contact']] as $i => $item)
                    <li class="rise border-b border-line" style="animation-delay: {{ $i * 60 }}ms">
                        <a href="{{ $item['href'] }}" class="flex items-center justify-between py-4 text-2xl font-light">
                            {{ $item['label'] }}
                            <x-arrow-circle />
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</header>
