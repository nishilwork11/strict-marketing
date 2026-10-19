@props(['title', 'description' => null, 'robots' => null, 'script' => 'resources/js/app.js'])
<!DOCTYPE html>
<html lang="en-GB" class="antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }}</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif
        @if ($robots)
            <meta name="robots" content="{{ $robots }}">
        @endif
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48" type="image/x-icon">
        <link rel="icon" href="{{ asset('icon.svg') }}" sizes="any" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-icon.png') }}" sizes="180x180" type="image/png">

        @fonts
        @vite(['resources/css/app.css', $script])
    </head>
    <body>
        {{ $slot }}
    </body>
</html>
