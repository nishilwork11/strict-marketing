{{-- The specialisms under the wordmark. Each word and dot is its own flex item, so
     justify-between spreads them evenly across whatever width the row is given.
     --chars (the letters across all of them) lets callers size the text to fit. --}}
@php
    $strapline = config('site.brand.strapline');
@endphp
<span {{ $attributes->class('flex whitespace-nowrap uppercase') }} style="--chars: {{ mb_strlen(implode('', $strapline)) }}">
    @foreach ($strapline as $item)
        @unless ($loop->first)
            <span aria-hidden="true">·</span>
        @endunless
        <span>{{ $item }}</span>
    @endforeach
</span>
