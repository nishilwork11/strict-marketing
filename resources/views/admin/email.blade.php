{{-- The mail settings the site is using, anything wrong with them, and a test email
     that goes out the same way as the enquiry emails (app/Support/MailCheck.php) --}}
@php
    $test = session('test');
@endphp
<x-layouts.admin :title="'Email · '.config('site.brand.name')">
    <x-admin.bar />
    <main class="mx-auto max-w-[1440px] px-4 pt-8 pb-16 sm:px-6 sm:pt-10 lg:px-8">
        <a href="{{ route('admin.enquiries.index') }}" class="group inline-flex items-center gap-2.5 text-[15px]">
            <x-arrow-circle direction="left" class="group-hover:-translate-x-0.5" />
            All enquiries
        </a>

        <h1 class="mt-8 text-[clamp(32px,3.4vw,48px)] leading-[1.1] font-light tracking-[-0.01em]">Email</h1>
        <p class="mt-2 max-w-[640px] text-[16px] text-slate">
            Each enquiry from the contact form is emailed to {{ config('site.contact.notify') }}. Send a test to check the website can send email.
        </p>

        @foreach ($problems as $problem)
            <div role="alert" class="mt-6 rounded-2xl border border-[#e3a200]/40 bg-[#e3a200]/10 px-5 py-4 text-[15px]">
                <p class="font-semibold">{{ $problem['title'] }}</p>
                <p class="mt-1 text-slate"><x-admin.code-text :text="$problem['body']" /></p>
                @if ($problem['items'])
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-slate">
                        @foreach ($problem['items'] as $item)
                            <li><x-admin.code-text :text="$item" /></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach

        <div class="mt-8 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_400px]">
            <section aria-labelledby="test-title" class="rounded-2xl bg-paper p-6 sm:p-8">
                <h2 id="test-title" class="text-[14px] font-medium text-slate">Send a test email</h2>

                @if ($test && ! $test['sent'])
                    <div role="alert" class="mt-4 rounded-xl border border-magenta/30 bg-magenta/6 px-5 py-4 text-[15px]">
                        <p class="font-semibold">Couldn't send it to {{ $test['to'] }}</p>
                        @if ($test['hint'])
                            <p class="mt-1"><x-admin.code-text :text="$test['hint']" /></p>
                        @endif
                        <p class="mt-4 text-[13px] font-medium text-slate">What the mail server said</p>
                        <p class="mt-1 font-mono text-[13px] break-words">{{ $test['error'] }}</p>
                    </div>
                @elseif ($test && ! $sends)
                    <div role="status" class="mt-4 rounded-xl border border-[#e3a200]/40 bg-[#e3a200]/10 px-5 py-4 text-[15px]">
                        <p class="font-semibold">Not sent</p>
                        <p class="mt-1 text-slate">
                            <x-admin.code>MAIL_MAILER</x-admin.code> is <x-admin.code>{{ config('mail.default') }}</x-admin.code>, so the test didn't leave the website. Set <x-admin.code>MAIL_MAILER=smtp</x-admin.code> to send it.
                        </p>
                    </div>
                @elseif ($test)
                    @php
                        $seconds = number_format($test['seconds'], 1);
                    @endphp
                    <div role="status" class="mt-4 rounded-xl border border-[#087a2a]/25 bg-[#087a2a]/10 px-5 py-4 text-[15px]">
                        <p class="font-semibold">Sent to {{ $test['to'] }}</p>
                        <p class="mt-1 text-slate">
                            {{-- Only an SMTP server's reply shows the email really left the website --}}
                            @if ($test['reply'])
                                The mail server took it in {{ $seconds }} seconds and replied “{{ $test['reply'] }}”.
                            @else
                                The site handed it over in {{ $seconds }} seconds.
                            @endif
                            It should arrive within a few minutes. If it doesn't, look in the junk folder, and check <x-admin.code>MAIL_HOST</x-admin.code> is the outgoing server of the email provider where you read this mailbox.
                        </p>
                    </div>
                @endif

                {{-- Shows "Sending…" while the test is on its way (resources/js/admin/email-test.js) --}}
                <form method="post" action="{{ route('admin.email.send') }}" data-email-test class="mt-6 flex flex-col gap-8">
                    @csrf
                    <div>
                        <label for="to" class="text-[14px] font-medium">Send it to</label>
                        <input
                            id="to"
                            name="to"
                            type="email"
                            autocomplete="email"
                            required
                            value="{{ old('to', config('site.contact.notify')) }}"
                            aria-invalid="{{ $errors->has('to') ? 'true' : 'false' }}"
                            @error('to') aria-describedby="to-error" @enderror
                            class="field-line mt-2 block w-full border-0 border-b border-line bg-transparent px-0 py-2.5 text-[18px] outline-none placeholder:text-grey aria-[invalid=true]:border-magenta"
                        >
                        <x-site.field-error name="to" />
                    </div>
                    <button
                        type="submit"
                        class="group inline-flex items-center gap-3 self-start rounded-full bg-ink py-2 pr-2 pl-6 text-[16px] text-paper transition-colors hover:bg-slate disabled:cursor-wait disabled:opacity-80"
                    >
                        <x-roll-label>Send test email</x-roll-label>
                        <x-arrow-circle inverted data-email-arrow class="size-8 group-hover:translate-x-0.5" />
                        <span aria-hidden="true" data-email-dots class="inline-flex size-8 items-center justify-center gap-[3px] rounded-full bg-paper" hidden>
                            <span class="ink-wait size-[5px] rounded-full bg-cyan" style="--d: 0"></span>
                            <span class="ink-wait size-[5px] rounded-full bg-magenta" style="--d: 1"></span>
                            <span class="ink-wait size-[5px] rounded-full bg-yellow" style="--d: 2"></span>
                        </span>
                    </button>
                </form>
            </section>

            <aside aria-labelledby="settings-title" class="rounded-2xl bg-paper p-6 sm:p-8">
                <h2 id="settings-title" class="text-[14px] font-medium text-slate">Settings in use</h2>
                <dl class="mt-3 divide-y divide-line/60">
                    @foreach ($settings as $label => $value)
                        <x-admin.detail-row :label="$label">
                            @if ($value === null)
                                <span class="text-slate">Not set</span>
                            @else
                                <span class="break-words">{{ $value }}</span>
                            @endif
                        </x-admin.detail-row>
                    @endforeach
                </dl>
                <p class="mt-5 border-t border-line/60 pt-5 text-[14px] text-slate">
                    From the <x-admin.code>MAIL_</x-admin.code> lines in .env on the server. The password itself is never shown.
                </p>
            </aside>
        </div>
    </main>
</x-layouts.admin>
