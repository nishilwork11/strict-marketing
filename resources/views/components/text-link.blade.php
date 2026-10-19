{{-- A text link with Bou's arrow circle. An external link opens in a new tab, with its arrow pointing out. --}}
@props(['href', 'inverted' => false, 'external' => false])
<a
    href="{{ $href }}"
    @if ($external) target="_blank" rel="noopener" @endif
    {{ $attributes->class(['group inline-flex items-center gap-2.5 text-[15px]', 'text-paper' => $inverted, 'text-ink' => ! $inverted]) }}
>
    <x-roll-label>{{ $slot }}</x-roll-label>
    @if ($external)
        <span class="sr-only">(opens in a new tab)</span>
    @endif
    <x-arrow-circle :inverted="$inverted" @class(['group-hover:translate-x-1', '-rotate-45' => $external]) />
</a>
