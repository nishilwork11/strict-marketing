<?php

namespace Tests\Unit;

use App\Support\AdminDates;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class AdminDatesTest extends TestCase
{
    private function utc(string $time): CarbonImmutable
    {
        return CarbonImmutable::parse($time, 'UTC');
    }

    public function test_list_labels_follow_the_age_of_the_enquiry(): void
    {
        $now = $this->utc('2026-10-19 10:00'); // a Monday, in British Summer Time

        $this->assertSame('09:30', AdminDates::short($this->utc('2026-10-19 08:30'), $now));
        $this->assertSame('Yesterday', AdminDates::short($this->utc('2026-10-18 09:00'), $now));
        $this->assertSame('Thu', AdminDates::short($this->utc('2026-10-15 11:24'), $now));
        $this->assertSame('3 Sept', AdminDates::short($this->utc('2026-09-03 12:00'), $now));
        $this->assertSame('3 Sept 2025', AdminDates::short($this->utc('2025-09-03 12:00'), $now));
    }

    public function test_full_and_long_dates_read_in_uk_time(): void
    {
        $this->assertSame('Thursday, 15 October 2026 at 12:24', AdminDates::full($this->utc('2026-10-15 11:24')));
        $this->assertSame('Tuesday, 15 December 2026 at 11:24', AdminDates::full($this->utc('2026-12-15 11:24')));
        $this->assertSame('3 June 2026', AdminDates::long($this->utc('2026-06-03 12:00')));
    }

    public function test_time_ago_matches_the_browsers_wording(): void
    {
        $now = $this->utc('2026-10-19 10:00');

        $this->assertSame('just now', AdminDates::ago($now->subSeconds(20), $now));
        $this->assertSame('1 minute ago', AdminDates::ago($now->subMinute(), $now));
        $this->assertSame('5 minutes ago', AdminDates::ago($now->subMinutes(5), $now));
        $this->assertSame('1 hour ago', AdminDates::ago($now->subHour(), $now));
        $this->assertSame('yesterday', AdminDates::ago($now->subDay(), $now));
        $this->assertSame('3 days ago', AdminDates::ago($now->subDays(3), $now));
        $this->assertSame('last month', AdminDates::ago($now->subDays(31), $now));
        $this->assertSame('2 months ago', AdminDates::ago($now->subDays(61), $now));
        $this->assertSame('last year', AdminDates::ago($now->subDays(365), $now));
        $this->assertSame('4 years ago', AdminDates::ago($now->subDays(4 * 365), $now));
    }

    public function test_the_chart_counts_by_uk_calendar_day(): void
    {
        $now = $this->utc('2026-10-19 10:00');
        // 23:30 UTC on the 18th is already the 19th in the UK
        $days = AdminDates::countByDay([$this->utc('2026-10-18 23:30'), $this->utc('2026-10-18 12:00')], $now, 14);

        $this->assertCount(14, $days);
        $this->assertSame(['key' => '2026-10-06', 'label' => 'Tue 6 Oct', 'short' => '6 Oct', 'count' => 0], $days[0]);
        $this->assertSame(1, $days[12]['count']);
        $this->assertSame(['key' => '2026-10-19', 'label' => 'Mon 19 Oct', 'short' => '19 Oct', 'count' => 1], $days[13]);
    }
}
