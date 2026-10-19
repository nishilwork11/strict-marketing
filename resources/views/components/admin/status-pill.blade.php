@props(['status'])
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[12px] font-medium', $status->tint()]) }}>
    <x-admin.status-dot :status="$status" />
    {{ $status->label() }}
</span>
