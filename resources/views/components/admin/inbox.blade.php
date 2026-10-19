@php
    $cell = 'px-3 py-4 align-top first:pl-5 last:pr-5';
    $head = 'px-3 py-3 font-medium whitespace-nowrap first:pl-5 last:pr-5';
@endphp
{{-- Clicking anywhere on a row opens that enquiry's page --}}
<section aria-label="Enquiries" data-inbox class="mt-8 overflow-hidden rounded-2xl bg-paper">
    <div class="flex flex-col gap-3 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative lg:w-[340px]">
            <svg
                aria-hidden="true"
                viewBox="0 0 20 20"
                class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-slate"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
            >
                <circle cx="8.5" cy="8.5" r="5.5" />
                <path d="M13 13l4 4" />
            </svg>
            <input
                type="search"
                value="{{ $query }}"
                data-inbox-search
                placeholder="Search names, emails and messages"
                aria-label="Search enquiries"
                class="w-full rounded-full border border-line bg-paper py-2.5 pr-4 pl-11 text-[15px] transition-colors outline-none placeholder:text-grey focus:border-ink"
            >
        </div>
        <div role="group" aria-label="Show" class="flex flex-wrap gap-1.5">
            @foreach ([null, ...$statuses] as $status)
                <button
                    type="button"
                    data-inbox-filter="{{ $status?->value }}"
                    data-label="{{ $status?->label() }}"
                    aria-pressed="{{ $filter === $status ? 'true' : 'false' }}"
                    class="inline-flex items-center gap-2 rounded-full border border-line bg-paper px-3.5 py-1.5 text-[14px] transition-colors hover:border-ink aria-pressed:border-ink aria-pressed:bg-ink aria-pressed:text-paper"
                >
                    @if ($status)
                        <x-admin.status-dot :status="$status" />
                    @endif
                    {{ $status ? $status->label() : 'All' }}
                    <span data-inbox-count class="tabular-nums opacity-60">{{ $counts[$status?->value ?? ''] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <p aria-live="polite" data-inbox-announce class="sr-only">{{ $shown }} {{ $shown === 1 ? 'enquiry' : 'enquiries' }} shown</p>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[14px]">
            <thead class="border-y border-line/60 bg-mist/40 text-[13px] text-slate">
                <tr>
                    <th scope="col" class="{{ $head }}">Name</th>
                    <th scope="col" class="{{ $head }} hidden md:table-cell">Email</th>
                    <th scope="col" class="{{ $head }} hidden lg:table-cell">Phone</th>
                    <th scope="col" class="{{ $head }} hidden xl:table-cell">Interested in</th>
                    <th scope="col" class="{{ $head }} hidden lg:table-cell">Budget</th>
                    <th scope="col" class="{{ $head }} hidden sm:table-cell">Status</th>
                    <th scope="col" class="{{ $head }} text-right">Received</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php
                        $e = $row['enquiry'];
                        $isNew = $e->status === \App\Enums\EnquiryStatus::New;
                    @endphp
                    {{-- Rows the search or filter leave out are hidden, so the last one shown drops its rule --}}
                    <tr
                        data-inbox-row
                        data-href="{{ $row['href'] }}"
                        data-status="{{ $e->status->value }}"
                        data-search="{{ $row['search'] }}"
                        @unless ($row['shown']) hidden @endunless
                        class="cursor-pointer border-b border-line/60 transition-colors hover:bg-mist/40 [&:not(:has(~tr:not([hidden])))]:border-b-0"
                    >
                        {{-- Takes whatever width is left, and shortens long names rather than widening the table --}}
                        <td class="{{ $cell }} w-full max-w-0 min-w-[150px]">
                            <div class="flex items-center gap-2">
                                @if ($isNew)
                                    <span aria-hidden="true" class="size-2 shrink-0 rounded-full bg-cyan"></span>
                                @endif
                                <a
                                    href="{{ $row['href'] }}"
                                    @class(['truncate text-[15px] underline-offset-2 hover:underline', 'font-semibold' => $isNew, 'font-medium' => ! $isNew])
                                >{{ $e->name }}</a>
                            </div>
                            @if ($e->company)
                                <p class="mt-0.5 truncate text-[13px] text-slate">{{ $e->company }}</p>
                            @else
                                <p class="mt-0.5 truncate text-[13px] text-slate md:hidden">{{ $e->email }}</p>
                            @endif
                            <div class="mt-2 sm:hidden">
                                <x-admin.status-pill :status="$e->status" />
                            </div>
                        </td>
                        <td class="{{ $cell }} hidden md:table-cell">
                            <a href="mailto:{{ $e->email }}" class="block max-w-[220px] truncate underline-offset-2 hover:underline">{{ $e->email }}</a>
                        </td>
                        <td class="{{ $cell }} hidden whitespace-nowrap lg:table-cell">
                            @if ($e->phone)
                                {{ $e->phone }}
                            @else
                                <span class="text-grey">—</span>
                            @endif
                        </td>
                        <td class="{{ $cell }} hidden xl:table-cell">
                            @if ($e->services)
                                <span class="flex max-w-[220px] flex-wrap gap-1">
                                    @foreach ($e->services as $service)
                                        <span class="rounded-full border border-line px-2 py-0.5 text-[12px] whitespace-nowrap">{{ $service }}</span>
                                    @endforeach
                                </span>
                            @else
                                <span class="text-grey">—</span>
                            @endif
                        </td>
                        <td class="{{ $cell }} hidden whitespace-nowrap lg:table-cell">
                            @if ($e->budget)
                                {{ $e->budget }}
                            @else
                                <span class="text-grey">—</span>
                            @endif
                        </td>
                        <td class="{{ $cell }} hidden sm:table-cell">
                            <x-admin.status-pill :status="$e->status" />
                        </td>
                        <td class="{{ $cell }} text-right whitespace-nowrap text-slate">
                            <time datetime="{{ $e->received_at->toIso8601ZuluString() }}" title="{{ $row['full'] }}">{{ $row['short'] }}</time>
                        </td>
                    </tr>
                @endforeach
                <tr data-inbox-empty @if ($shown > 0) hidden @endif>
                    <td colspan="7" data-inbox-empty-text class="px-5 py-14 text-center text-[15px] text-slate">{{ $emptyMessage() }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>
