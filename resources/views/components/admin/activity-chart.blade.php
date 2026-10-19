{{-- Enquiries per day for two weeks: the last 7 days in ink, the week before in grey.
     Hovering a day shows its count; the table underneath holds every value. --}}
@props(['days'])
@php
    $peak = max(1, ...array_column($days, 'count'));
    $last = count($days) - 1;
@endphp
<figure class="mt-auto pt-5">
    <div aria-hidden="true" class="flex h-16 items-end gap-[2px] border-b border-line">
        @foreach ($days as $i => $day)
            <div class="group relative flex h-full flex-1 items-end justify-center">
                <div
                    @class(['w-full max-w-6 rounded-t-[4px] transition-opacity group-hover:opacity-70', 'bg-ink' => $i > $last - 7, 'bg-grey' => $i <= $last - 7])
                    style="height: {{ $day['count'] / $peak * 100 }}%"
                ></div>
                {{-- Tooltips near the ends open inwards so they never hang off the tile --}}
                <p @class([
                    'pointer-events-none absolute bottom-full z-10 mb-2 hidden rounded-md bg-ink px-2.5 py-1.5 text-[12px] whitespace-nowrap text-paper shadow-lg group-hover:block',
                    'left-0' => $i < 3,
                    'right-0' => $i > $last - 3,
                    'left-1/2 -translate-x-1/2' => $i >= 3 && $i <= $last - 3,
                ])><span class="font-semibold">{{ $day['count'] }}</span> · {{ $day['label'] }}</p>
            </div>
        @endforeach
    </div>
    <figcaption class="mt-1.5 flex justify-between text-[12px] text-slate">
        <span>{{ $days[0]['short'] }}</span>
        <span>Today</span>
    </figcaption>
    <details class="mt-3 text-[13px]">
        <summary class="cursor-pointer text-slate hover:text-ink">Show as a table</summary>
        <table class="mt-2 w-full text-left">
            <thead class="text-slate">
                <tr>
                    <th scope="col" class="py-1 font-medium">Day</th>
                    <th scope="col" class="py-1 text-right font-medium">Enquiries</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($days as $day)
                    <tr class="border-t border-line/60">
                        <td class="py-1">{{ $day['label'] }}</td>
                        <td class="py-1 text-right tabular-nums">{{ $day['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </details>
</figure>
