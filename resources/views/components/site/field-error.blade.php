@props(['name'])
<p id="{{ $name }}-error" data-error-for="{{ $name }}" class="mt-2 text-[14px] text-magenta" @unless ($errors->has($name)) hidden @endunless>{{ $errors->first($name) }}</p>
