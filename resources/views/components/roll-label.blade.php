{{-- A button label that rolls up on hover while a copy follows it in from below.
     Place it directly inside the <a> or <button>; the CSS is in app.css under "Roll labels". --}}
<span class="roll"><span class="roll-a">{{ $slot }}</span><span aria-hidden="true" class="roll-b">{{ $slot }}</span></span>
