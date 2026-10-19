{{-- A checkbox or radio set as a pill; a tick opens inside it when it's selected --}}
@props(['type', 'name', 'value', 'checked' => false])
<label class="cursor-pointer">
    <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" @checked($checked) class="peer sr-only">
    <span class="inline-flex items-center rounded-full border border-ink/30 px-4 py-2 text-[15px] transition-colors peer-checked:border-ink peer-checked:bg-ink peer-checked:text-paper peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-cyan hover:border-ink">
        <svg aria-hidden="true" viewBox="0 0 16 16" class="pill-check h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 8.5l3.2 3.2L13 4.8" />
        </svg>
        {{ $value }}
    </span>
</label>
