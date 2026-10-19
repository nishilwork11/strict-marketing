<?php

namespace App\View\Components\Admin;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Support\AdminDates;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

// All enquiries in a table. Search and the status filter run in the browser so they're
// instant (resources/js/admin/inbox.js); this renders the view the address bar asks for.
class Inbox extends Component
{
    /** @var list<array{enquiry: Enquiry, href: string, search: string, short: string, full: string, shown: bool}> */
    public array $rows = [];

    /** @var array<string, int> Matching enquiries per filter button, keyed by status ('' for All) */
    public array $counts = [];

    public int $shown = 0;

    /** @var list<EnquiryStatus> */
    public array $statuses;

    /**
     * Create a new component instance.
     *
     * @param  Collection<int, Enquiry>  $enquiries  Newest first
     */
    public function __construct(Collection $enquiries, DateTimeInterface $now, public ?EnquiryStatus $filter, public string $query)
    {
        $this->statuses = EnquiryStatus::cases();
        $this->counts = ['' => 0] + array_fill_keys(array_map(fn ($status) => $status->value, $this->statuses), 0);
        $q = mb_strtolower($query);

        foreach ($enquiries as $enquiry) {
            $search = $enquiry->searchText();
            $matches = $q === '' || str_contains($search, $q);
            $shown = $matches && (! $filter || $enquiry->status === $filter);

            if ($matches) {
                $this->counts['']++;
                $this->counts[$enquiry->status->value]++;
            }
            $this->shown += (int) $shown;

            $this->rows[] = [
                'enquiry' => $enquiry,
                'href' => route('admin.enquiries.show', $enquiry),
                'search' => $search,
                // Date labels are worked out here, so every visitor sees the same UK time
                'short' => AdminDates::short($enquiry->received_at, $now),
                'full' => AdminDates::full($enquiry->received_at),
                'shown' => $shown,
            ];
        }
    }

    // "Nothing matches “acme”." or "No won enquiries."
    public function emptyMessage(): string
    {
        return $this->query !== ''
            ? "Nothing matches “{$this->query}”."
            : 'No '.($this->filter ? mb_strtolower($this->filter->label()).' ' : '').'enquiries.';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.inbox');
    }
}
