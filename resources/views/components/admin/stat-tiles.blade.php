@php
    $tile = 'flex flex-col rounded-2xl p-5 sm:p-6';
    $label = 'text-[14px] font-medium';
    $figure = 'mt-4 text-[44px] leading-none font-light tracking-[-0.02em]';
    $total = $enquiries->count();
@endphp
<section aria-label="Summary" class="mt-8 grid grid-cols-2 gap-3 xl:grid-cols-4">
    <div class="{{ $tile }} col-span-2 bg-ink text-paper sm:col-span-1">
        <div class="flex items-center justify-between gap-3">
            <h2 class="{{ $label }} text-paper/70">Waiting for a reply</h2>
            <span aria-hidden="true" class="flex gap-1">
                <span class="size-1.5 rounded-full bg-cyan"></span>
                <span class="size-1.5 rounded-full bg-magenta"></span>
                <span class="size-1.5 rounded-full bg-yellow"></span>
            </span>
        </div>
        <p class="mt-4 text-[64px] leading-none font-light tracking-[-0.02em]">{{ $waiting }}</p>
        <p class="mt-auto pt-6 text-[14px] text-paper/70">{{ $oldestWaiting ? "Oldest arrived {$oldestWaiting}" : "You're all caught up" }}</p>
    </div>

    <div class="{{ $tile }} col-span-2 bg-paper sm:col-span-1">
        <h2 class="{{ $label }} text-slate">Last 7 days</h2>
        <p class="{{ $figure }}">{{ $lastWeek }}</p>
        @if ($change === 0)
            <p class="mt-2.5 text-[14px] text-slate">Same as the week before</p>
        @else
            <p class="mt-2.5 flex items-center gap-1.5 text-[14px] text-slate">
                <svg aria-hidden="true" viewBox="0 0 10 10" @class(['size-2.5', 'text-[#087a2a]' => $change > 0, 'rotate-180 text-[#d03b3b]' => $change < 0])>
                    <path d="M5 1l4 7H1z" fill="currentColor" />
                </svg>
                {{ abs($change) }} {{ $change > 0 ? 'more' : 'fewer' }} than the week before
            </p>
        @endif
        <x-admin.activity-chart :days="$days" />
    </div>

    <div class="{{ $tile }} bg-paper">
        <h2 class="{{ $label }} text-slate">Won</h2>
        <p class="{{ $figure }}">{{ $counts['won'] }}</p>
        <div class="mt-auto pt-6">
            <div aria-hidden="true" class="h-2 overflow-hidden rounded-full bg-[#087a2a]/15">
                <div class="h-full rounded-full bg-[#087a2a]" style="width: {{ $closed ? $counts['won'] / $closed * 100 : 0 }}%"></div>
            </div>
            <p class="mt-2.5 text-[14px] text-slate">
                {{ $closed ? round($counts['won'] / $closed * 100).'% of closed enquiries' : 'None closed yet' }}
            </p>
        </div>
    </div>

    <div class="{{ $tile }} bg-paper">
        <h2 class="{{ $label }} text-slate">All enquiries</h2>
        <p class="{{ $figure }}">{{ $total }}</p>
        <p class="mt-2 text-[14px] text-slate">{{ $since ? "Since {$since}" : 'None yet' }}</p>
        <div class="mt-auto pt-5">
            {{-- Share of each status; the gaps between segments are the tile showing through --}}
            <div aria-hidden="true" @class(['flex h-2 gap-[2px] overflow-hidden rounded-full', 'bg-mist' => $total === 0])>
                @foreach ($statuses as $status)
                    @if ($counts[$status->value] > 0)
                        <span class="h-full basis-0 {{ $status->color() }}" style="flex-grow: {{ $counts[$status->value] }}"></span>
                    @endif
                @endforeach
            </div>
            <ul class="mt-3 grid gap-x-4 gap-y-1 text-[13px] sm:grid-cols-2">
                @foreach ($statuses as $status)
                    <li class="flex items-center gap-1.5">
                        <x-admin.status-dot :status="$status" />
                        <span class="text-slate">{{ $status->label() }}</span>
                        <span class="ml-auto tabular-nums">{{ $counts[$status->value] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
