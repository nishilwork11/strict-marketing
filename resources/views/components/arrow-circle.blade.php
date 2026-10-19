@props(['inverted' => false, 'direction' => 'right'])
{{-- The black circle with an arrow that Bou pairs with its text links --}}
<span aria-hidden="true" {{ $attributes->class([
    'inline-flex size-6 shrink-0 items-center justify-center rounded-full transition-transform duration-300 ease-(--ease-bou)',
    'bg-paper text-ink' => $inverted,
    'bg-ink text-paper' => ! $inverted,
]) }}>
    <svg viewBox="0 0 16 16" @class(['size-3.5', 'rotate-180' => $direction === 'left']) fill="none" stroke="currentColor" stroke-width="1.6">
        <path d="M3 8h10M9 4l4 4-4 4" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</span>
