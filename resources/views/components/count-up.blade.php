{{-- Counts the number inside a figure up from zero the first time it is seen
     (resources/js/motion/count-up.js). The server renders the final value, so it
     reads correctly without JavaScript. --}}
@props(['value'])
<span class="tabular-nums" data-count-up="{{ $value }}">{{ $value }}</span>
