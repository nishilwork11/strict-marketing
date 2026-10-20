{{--
    The enquiry form. resources/js/site/contact-form.js sends it with fetch and shows
    the answer in place; without JavaScript it posts normally and the page comes
    back with the same messages, or with the "Enquiry sent" panel.
--}}
@php
    $contact = config('site.contact');
    $sent = session('enquiry_sent');
    $inputClass = 'field-line mt-2 block w-full border-0 border-b border-line bg-transparent px-0 py-2.5 text-[18px] outline-none placeholder:text-grey aria-[invalid=true]:border-magenta';
@endphp
<div data-contact>
    <div role="status" data-contact-sent class="flex min-h-[420px] flex-col items-start justify-center" @unless ($sent) hidden @endunless>
        <span aria-hidden="true" class="rise mb-6 inline-flex size-14 items-center justify-center rounded-full bg-ink text-paper">
            <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path class="check-draw" d="M5 12.5l4.5 4.5L19 7.5" />
            </svg>
        </span>
        <p class="rise font-display text-[clamp(26px,2.4vw,36px)] leading-tight font-light" style="animation-delay: 120ms">Enquiry sent.</p>
        <p class="rise mt-3 text-[17px] text-slate" style="animation-delay: 220ms" data-contact-sent-message>{{ $sent }}</p>
        <a
            href="{{ url('/') }}#contact"
            data-contact-again
            data-magnetic
            class="mt-8 rounded-full border border-ink px-5 py-2.5 text-[15px] transition-colors hover:bg-ink hover:text-paper"
        >
            <x-roll-label>Send another enquiry</x-roll-label>
        </a>
    </div>

    <form
        method="post"
        action="{{ route('contact.store') }}"
        novalidate
        data-contact-form
        data-failed-message="Your enquiry could not be sent. Try again, or email us at {{ config('site.brand.email') }}."
        class="flex flex-col gap-8"
        @if ($sent) hidden @endif
    >
        @csrf
        <div class="grid gap-8 sm:grid-cols-2">
            <div>
                <label for="name" class="text-[14px] font-medium">Name</label>
                <input
                    id="name"
                    name="name"
                    autocomplete="name"
                    required
                    value="{{ old('name') }}"
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    @error('name') aria-describedby="name-error" @enderror
                    class="{{ $inputClass }}"
                >
                <x-site.field-error name="name" />
            </div>
            <div>
                <label for="email" class="text-[14px] font-medium">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    value="{{ old('email') }}"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    @error('email') aria-describedby="email-error" @enderror
                    class="{{ $inputClass }}"
                >
                <x-site.field-error name="email" />
            </div>
            <div>
                <label for="phone" class="text-[14px] font-medium">Phone</label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    autocomplete="tel"
                    required
                    value="{{ old('phone') }}"
                    aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                    @error('phone') aria-describedby="phone-error" @enderror
                    class="{{ $inputClass }}"
                >
                <x-site.field-error name="phone" />
            </div>
            <div>
                <label for="company" class="text-[14px] font-medium">
                    Company <span class="font-normal text-grey">(optional)</span>
                </label>
                <input id="company" name="company" autocomplete="organization" value="{{ old('company') }}" class="{{ $inputClass }}">
            </div>
        </div>

        <fieldset>
            <legend class="text-[14px] font-medium">What do you need help with?</legend>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($contact['services'] as $service)
                    <x-site.pill type="checkbox" name="services[]" :value="$service" :checked="in_array($service, (array) old('services', []), true)" />
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="text-[14px] font-medium">
                Budget <span class="font-normal text-grey">(optional)</span>
            </legend>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($contact['budgets'] as $budget)
                    <x-site.pill type="radio" name="budget" :value="$budget" :checked="old('budget') === $budget" />
                @endforeach
            </div>
        </fieldset>

        <div>
            <label for="message" class="text-[14px] font-medium">About the project</label>
            <textarea
                id="message"
                name="message"
                rows="5"
                required
                placeholder="What are you trying to achieve, and by when?"
                aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}"
                @error('message') aria-describedby="message-error" @enderror
                class="{{ $inputClass }} resize-y"
            >{{ old('message') }}</textarea>
            <x-site.field-error name="message" />
        </div>

        {{-- Honeypot: hidden from people and screen readers --}}
        <div aria-hidden="true" class="absolute -left-[9999px] h-0 w-0 overflow-hidden">
            <label for="website">Leave this empty</label>
            <input id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
            <button
                type="submit"
                data-magnetic
                data-contact-submit
                class="group inline-flex items-center gap-3 rounded-full bg-ink py-2 pr-2 pl-6 text-[16px] text-paper transition-colors hover:bg-slate disabled:cursor-wait disabled:opacity-80"
            >
                <x-roll-label>Send enquiry</x-roll-label>
                <x-arrow-circle inverted data-contact-arrow class="size-8 group-hover:translate-x-0.5" />
                {{-- Takes the arrow's place while the enquiry is on its way:
                     the cyan, magenta and yellow dots lift one after another --}}
                <span aria-hidden="true" data-contact-dots class="inline-flex size-8 items-center justify-center gap-[3px] rounded-full bg-paper" hidden>
                    <span class="ink-wait size-[5px] rounded-full bg-cyan" style="--d: 0"></span>
                    <span class="ink-wait size-[5px] rounded-full bg-magenta" style="--d: 1"></span>
                    <span class="ink-wait size-[5px] rounded-full bg-yellow" style="--d: 2"></span>
                </span>
            </button>
            <p role="status" aria-live="polite" data-contact-status class="text-[14px] text-magenta">{{ session('enquiry_error') }}</p>
        </div>
    </form>
</div>
