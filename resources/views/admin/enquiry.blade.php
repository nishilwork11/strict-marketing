@use('App\Enums\EnquiryStatus')
@use('App\Support\AdminDates')
<x-layouts.admin>
    <x-admin.bar />
    <main class="mx-auto max-w-[1440px] px-4 pt-8 pb-16 sm:px-6 sm:pt-10 lg:px-8">
        <a href="{{ route('admin.enquiries.index') }}" class="group inline-flex items-center gap-2.5 text-[15px]">
            <x-arrow-circle direction="left" class="group-hover:-translate-x-0.5" />
            All enquiries
        </a>

        @if (! $enquiry)
            <div class="mt-8 flex flex-col items-center rounded-2xl bg-paper px-6 py-16 text-center">
                <h1 class="text-[28px] leading-tight font-light">This enquiry isn't here</h1>
                <p class="mt-2 text-[16px] text-slate">It may have been deleted.</p>
                <a href="{{ route('admin.enquiries.index') }}" class="link-line mt-6 text-[15px]">Back to all enquiries</a>
            </div>
        @else
            @php
                $e = $enquiry;
                $tel = preg_replace('/[^\d+]/', '', $e->phone);
                $reply = 'mailto:'.$e->email.'?subject='.rawurlencode('Re: your enquiry to '.config('site.brand.name'));
                $received = $e->received_at->toIso8601ZuluString();
            @endphp
            {{-- One enquiry with everything that was sent, its status, and ways to reply --}}
            <article class="mt-8">
                <header class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[14px] text-slate">
                            <time datetime="{{ $received }}">{{ AdminDates::full($e->received_at) }}</time> · {{ AdminDates::ago($e->received_at, $now) }}
                        </p>
                        <h1 class="mt-2 text-[clamp(34px,4vw,56px)] leading-[1.05] font-light tracking-[-0.015em] break-words">{{ $e->name }}</h1>
                        @if ($e->company)
                            <p class="mt-2 text-[18px] text-slate">{{ $e->company }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a
                            href="{{ $reply }}"
                            class="group inline-flex items-center gap-3 rounded-full bg-ink py-2 pr-2 pl-5 text-[15px] text-paper transition-colors hover:bg-slate"
                        >
                            <x-roll-label>Reply by email</x-roll-label>
                            <x-arrow-circle inverted class="size-7 group-hover:translate-x-0.5" />
                        </a>
                        @if ($e->phone)
                            <a
                                href="tel:{{ $tel }}"
                                class="inline-flex items-center rounded-full border border-ink px-5 py-2.5 text-[15px] transition-colors hover:bg-ink hover:text-paper"
                            >
                                <x-roll-label>Call</x-roll-label>
                            </a>
                        @endif
                    </div>
                </header>

                <div class="mt-8">
                    <x-admin.status-picker :enquiry="$e" />
                </div>

                <div class="mt-8 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_400px]">
                    <section aria-labelledby="message-title" class="rounded-2xl bg-paper p-6 sm:p-8">
                        <h2 id="message-title" class="text-[14px] font-medium text-slate">Message</h2>
                        <p class="mt-4 text-[18px] leading-relaxed break-words whitespace-pre-wrap">{{ $e->message }}</p>
                    </section>

                    <aside aria-labelledby="details-title" class="rounded-2xl bg-paper p-6 sm:p-8">
                        <h2 id="details-title" class="text-[14px] font-medium text-slate">Details</h2>
                        <dl class="mt-3 divide-y divide-line/60">
                            <x-admin.detail-row label="Name">{{ $e->name }}</x-admin.detail-row>
                            <x-admin.detail-row label="Email">
                                <a href="mailto:{{ $e->email }}" class="link-line break-all">{{ $e->email }}</a>
                            </x-admin.detail-row>
                            <x-admin.detail-row label="Phone">
                                @if ($e->phone)
                                    <a href="tel:{{ $tel }}" class="link-line">{{ $e->phone }}</a>
                                @else
                                    <span class="text-slate">Not given</span>
                                @endif
                            </x-admin.detail-row>
                            <x-admin.detail-row label="Company">
                                @if ($e->company)
                                    {{ $e->company }}
                                @else
                                    <span class="text-slate">Not given</span>
                                @endif
                            </x-admin.detail-row>
                            <x-admin.detail-row label="Interested in">
                                @if ($e->services)
                                    <span class="flex flex-wrap gap-1.5">
                                        @foreach ($e->services as $service)
                                            <span class="rounded-full border border-line px-2.5 py-0.5 text-[13px]">{{ $service }}</span>
                                        @endforeach
                                    </span>
                                @else
                                    <span class="text-slate">Not given</span>
                                @endif
                            </x-admin.detail-row>
                            <x-admin.detail-row label="Budget">
                                @if ($e->budget)
                                    {{ $e->budget }}
                                @else
                                    <span class="text-slate">Not given</span>
                                @endif
                            </x-admin.detail-row>
                            <x-admin.detail-row label="Status">
                                {{-- One pill per status; the status picker shows the one that's set --}}
                                @foreach (EnquiryStatus::cases() as $status)
                                    <x-admin.status-pill :status="$status" data-status-pill="{{ $status->value }}" :hidden="$e->status !== $status" />
                                @endforeach
                            </x-admin.detail-row>
                            <x-admin.detail-row label="Received">
                                <time datetime="{{ $received }}">{{ AdminDates::full($e->received_at) }}</time>
                            </x-admin.detail-row>
                        </dl>
                        <div class="mt-6 border-t border-line/60 pt-5">
                            <x-admin.delete-button :enquiry="$e" />
                        </div>
                    </aside>
                </div>
            </article>
        @endif
    </main>
</x-layouts.admin>
