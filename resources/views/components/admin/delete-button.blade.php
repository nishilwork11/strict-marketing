{{-- Deleting can't be undone, so the first press only arms the button and a second
     press within a few seconds deletes (resources/js/admin/delete-button.js) --}}
@props(['enquiry'])
<form method="post" action="{{ route('admin.enquiries.destroy', $enquiry) }}" data-delete-form>
    @csrf
    @method('DELETE')
    <button
        type="submit"
        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[14px] transition-colors disabled:cursor-wait text-slate hover:bg-mist hover:text-ink"
    >
        <svg aria-hidden="true" viewBox="0 0 16 16" class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2.5 4h11M6.5 4V2.5h3V4M4 4l.7 9.5h6.6L12 4" />
        </svg>
        <span data-delete-label>Delete enquiry</span>
    </button>
</form>
