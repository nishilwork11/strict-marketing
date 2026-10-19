@props(['title' => 'Enquiries · '.config('site.brand.name')])
<x-layouts.app :title="$title" robots="noindex, nofollow" script="resources/js/admin.js">
    <div class="min-h-svh bg-mist/50">
        {{ $slot }}
    </div>
</x-layouts.app>
