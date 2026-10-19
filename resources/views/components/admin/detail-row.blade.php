@props(['label'])
<div class="grid grid-cols-[112px_minmax(0,1fr)] gap-4 py-3.5 text-[15px]">
    <dt class="text-slate">{{ $label }}</dt>
    <dd class="min-w-0">{{ $slot }}</dd>
</div>
