{{-- Shows the new status straight away while the change saves (resources/js/admin/status-picker.js) --}}
@props(['enquiry'])
<form
    method="post"
    action="{{ route('admin.enquiries.update', $enquiry) }}"
    data-status-picker
    aria-label="Status"
    class="grid grid-cols-2 gap-1 rounded-2xl bg-mist p-1 sm:inline-grid sm:grid-cols-4 sm:rounded-full"
>
    @csrf
    @method('PATCH')
    @foreach (\App\Enums\EnquiryStatus::cases() as $status)
        <button
            type="submit"
            name="status"
            value="{{ $status->value }}"
            aria-pressed="{{ $enquiry->status === $status ? 'true' : 'false' }}"
            class="inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-[14px] transition-colors hover:bg-paper aria-pressed:bg-ink aria-pressed:text-paper"
        >
            <x-admin.status-dot :status="$status" />
            {{ $status->label() }}
        </button>
    @endforeach
</form>
