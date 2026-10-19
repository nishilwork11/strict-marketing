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

// The counts above the inbox: waiting for a reply, the last 7 days, won, and all enquiries
class StatTiles extends Component
{
    public int $waiting;

    public ?string $oldestWaiting;

    public ?string $since;

    /** @var list<array{key: string, label: string, short: string, count: int}> */
    public array $days;

    public int $lastWeek;

    public int $change;

    /** @var array<string, int> */
    public array $counts;

    public int $closed;

    /** @var list<EnquiryStatus> */
    public array $statuses;

    /**
     * Create a new component instance.
     *
     * @param  Collection<int, Enquiry>  $enquiries  Newest first
     */
    public function __construct(public Collection $enquiries, DateTimeInterface $now)
    {
        $waiting = $enquiries->where('status', EnquiryStatus::New);
        $this->waiting = $waiting->count();
        // The list is newest first, so the last of each is the oldest
        $this->oldestWaiting = $waiting->isNotEmpty() ? AdminDates::ago($waiting->last()->received_at, $now) : null;
        $this->since = $enquiries->isNotEmpty() ? AdminDates::long($enquiries->last()->received_at) : null;

        $this->days = AdminDates::countByDay($enquiries->pluck('received_at'), $now, 14);
        $daily = array_column($this->days, 'count');
        $this->lastWeek = array_sum(array_slice($daily, 7));
        $this->change = $this->lastWeek - array_sum(array_slice($daily, 0, 7));

        $this->statuses = EnquiryStatus::cases();
        foreach ($this->statuses as $status) {
            $this->counts[$status->value] = $enquiries->where('status', $status)->count();
        }
        $this->closed = $this->counts['won'] + $this->counts['lost'];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.stat-tiles');
    }
}
