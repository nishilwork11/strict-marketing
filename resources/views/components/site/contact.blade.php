@php
    $brand = config('site.brand');
    $contact = config('site.contact');
@endphp
<section id="contact" aria-labelledby="contact-title" class="page-x pb-[clamp(90px,8vw,160px)]">
    <div class="grid gap-12 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-20">
        <div>
            <x-split-words
                as="h2"
                id="contact-title"
                :text="$contact['headline']"
                class="block font-display text-[clamp(34px,3.4vw,54px)] leading-[1.1] font-light tracking-[-0.01em] text-balance"
            />
            <p data-reveal="up" style="--i: 2" class="mt-6 max-w-[420px] text-[17px] leading-relaxed text-slate">{{ $contact['note'] }}</p>

            <dl class="mt-12 grid gap-6 text-[17px] sm:grid-cols-2 lg:grid-cols-1">
                <div data-reveal="up" style="--i: 3">
                    <dt class="text-[14px] text-grey">Contact Email</dt>
                    <dd class="mt-1">
                        <a href="mailto:{{ $brand['email'] }}" class="link-line">{{ $brand['email'] }}</a>
                    </dd>
                </div>
                <div data-reveal="up" style="--i: 4">
                    <dt class="text-[14px] text-grey">Phone</dt>
                    <dd class="mt-1">
                        <a href="tel:{{ preg_replace('/\s/', '', $brand['phone']) }}" class="link-line">{{ $brand['phone'] }}</a>
                    </dd>
                </div>
                <div data-reveal="up" style="--i: 5">
                    <dt class="text-[14px] text-grey">Studio</dt>
                    <dd class="mt-1">
                        @foreach ($brand['address'] as $line)
                            <span class="block">{{ $line }}</span>
                        @endforeach
                    </dd>
                </div>
            </dl>
        </div>

        <div data-reveal="up" style="--i: 1" class="relative rounded bg-mist p-[clamp(22px,3vw,48px)]">
            <h3 class="mb-8 font-display text-[clamp(24px,2vw,30px)] font-light">{{ $contact['form_title'] }}</h3>
            <x-site.contact-form />
        </div>
    </div>
</section>
