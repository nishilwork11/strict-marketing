{{-- The full logo: the mark and wordmark, with the strapline spread exactly across
     both. It scales with the font size of whatever it sits in. --}}
<span class="inline-flex flex-col leading-none font-bold tracking-tight">
    <span class="flex items-baseline gap-[0.3em]">
        {{-- Sits on the baseline and rises to the cap height of the wordmark --}}
        <x-logo-mark class="size-[0.7em] shrink-0" />
        {{ config('site.brand.wordmark') }}
    </span>
    {{-- Takes no width of its own, so it spans exactly the mark and wordmark (about 7em);
         the more letters it holds, the smaller it sets so they still fit --}}
    <x-strapline class="mt-[0.5em] w-0 min-w-full justify-between text-[length:calc(7em/(var(--chars)*0.75))] font-medium tracking-[0.03em] opacity-75" />
</span>
