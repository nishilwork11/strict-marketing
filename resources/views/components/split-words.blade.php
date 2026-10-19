{{--
    Wraps each word so it can be animated on its own. Screen readers still read
    the sentence normally because the words stay in one text flow with spaces.

    mode="reveal": words slide up when the heading scrolls into view (default).
    mode="load": words come into focus once on page load (hero).
    mode="scrub": words darken one by one as the text scrolls through the viewport.
--}}
@props(['text', 'as' => 'span', 'mode' => 'reveal'])
@php
    $words = explode(' ', $text);
@endphp
<{{ $as }} {{ $attributes->class(['split-load' => $mode === 'load']) }} style="--n: {{ count($words) }}" @if ($mode === 'reveal') data-reveal="words" @elseif ($mode === 'scrub') data-scrub @endif>@foreach ($words as $i => $word)<span><span class="word"><span class="word-inner" style="--i: {{ $i }}">{{ $word }}</span></span>{{ $loop->last ? '' : ' ' }}</span>@endforeach</{{ $as }}>
