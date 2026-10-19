<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * Every enquiry in a table, with counts, a 14-day chart, search and status filters.
     */
    public function index(Request $request): View
    {
        return view('admin.index', [
            // Newest first
            'enquiries' => Enquiry::query()->orderByDesc('received_at')->orderByDesc('id')->get(),
            // The moment they were read, which the date labels and the chart count from
            'now' => now(),
            // Search and filter are kept in the address bar, so coming back from an enquiry returns to the same view
            'filter' => EnquiryStatus::tryFrom((string) $request->query('status')),
            'query' => trim((string) $request->query('q')),
        ]);
    }

    /**
     * One enquiry with everything that was sent, its status, and ways to reply.
     */
    public function show(string $id): Response
    {
        $enquiry = Enquiry::find($id);

        // A deleted enquiry's page says so, rather than a bare error page
        return response()->view('admin.enquiry', ['enquiry' => $enquiry, 'now' => now()], $enquiry ? 200 : 404);
    }

    /**
     * Set an enquiry's status: New, Contacted, Won or Lost.
     */
    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $status = EnquiryStatus::tryFrom((string) $request->input('status'));
        $enquiry = Enquiry::find($id);

        if ($status && $enquiry) {
            $enquiry->update(['status' => $status]);
        }

        return $request->expectsJson()
            ? response()->json(['status' => $enquiry?->status])
            : redirect()->route('admin.enquiries.show', $id);
    }

    /**
     * Delete an enquiry. Its page is gone, so back to the list.
     */
    public function destroy(string $id): RedirectResponse
    {
        Enquiry::whereKey($id)->delete();

        return redirect()->route('admin.enquiries.index');
    }
}
