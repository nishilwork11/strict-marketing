<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use IntlDateFormatter;
use IntlDatePatternGenerator;

// Date labels for the admin panel, always in UK time so they read the same
// whichever timezone the server runs in. ICU builds the patterns, as the
// browser's Intl API does, so "14:03", "16 Sept" and the rest match it exactly.
class AdminDates
{
    const ZONE = 'Europe/London';

    // For the list: "14:03" today, then "Yesterday", a weekday, and a date once it's a week old
    public static function short(DateTimeInterface $time, DateTimeInterface $now): string
    {
        $age = static::daysApart($time, $now);

        return match (true) {
            $age === 0 => static::format('jjmm', $time),
            $age === 1 => 'Yesterday',
            $age < 7 => static::format('EEE', $time),
            static::dayKey($time, 'Y') === static::dayKey($now, 'Y') => static::format('dMMM', $time),
            default => static::format('yMMMd', $time),
        };
    }

    // "Wednesday, 15 October 2026 at 14:03"
    public static function full(DateTimeInterface $time): string
    {
        return static::format('yMMMMEEEEdjjmm', $time);
    }

    // "3 June 2026"
    public static function long(DateTimeInterface $time): string
    {
        return static::format('yMMMMd', $time);
    }

    // "5 minutes ago", "yesterday", "3 days ago"
    public static function ago(DateTimeInterface $time, DateTimeInterface $now): string
    {
        $minutes = (int) round(($now->getTimestamp() - $time->getTimestamp()) / 60);
        if ($minutes < 1) {
            return 'just now';
        }
        if ($minutes < 60) {
            return static::relative($minutes, 'minute');
        }
        $hours = (int) round($minutes / 60);
        if ($hours < 24) {
            return static::relative($hours, 'hour');
        }
        $days = (int) round($hours / 24);
        if ($days < 30) {
            return static::relative($days, 'day');
        }
        $months = (int) round($days / 30.44);

        return $months < 12 ? static::relative($months, 'month') : static::relative((int) round($months / 12), 'year');
    }

    /**
     * One entry per UK calendar day, oldest first, ending today.
     *
     * @param  iterable<DateTimeInterface>  $times
     * @return list<array{key: string, label: string, short: string, count: int}>
     */
    public static function countByDay(iterable $times, DateTimeInterface $now, int $days): array
    {
        $counts = [];
        foreach ($times as $time) {
            $key = static::dayKey($time);
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        // Midnight UTC of today's UK date; stepping back whole days in UTC never trips over clock changes
        $today = CarbonImmutable::parse(static::dayKey($now), 'UTC');

        return array_map(function (int $i) use ($today, $days, $counts) {
            $date = $today->subDays($days - 1 - $i);
            $key = $date->format('Y-m-d');

            return [
                'key' => $key,
                'label' => static::format('MMMEd', $date, 'UTC'),
                'short' => static::format('MMMd', $date, 'UTC'),
                'count' => $counts[$key] ?? 0,
            ];
        }, range(0, $days - 1));
    }

    // "2026-10-15": the UK calendar day a moment falls on
    protected static function dayKey(DateTimeInterface $time, string $format = 'Y-m-d'): string
    {
        return CarbonImmutable::instance($time)->setTimezone(static::ZONE)->format($format);
    }

    protected static function daysApart(DateTimeInterface $from, DateTimeInterface $to): int
    {
        return (int) CarbonImmutable::parse(static::dayKey($from), 'UTC')
            ->diffInDays(CarbonImmutable::parse(static::dayKey($to), 'UTC'));
    }

    // numeric: "auto" wording, as the browser's Intl.RelativeTimeFormat gives it
    protected static function relative(int $count, string $unit): string
    {
        return match (true) {
            $count === 1 && $unit === 'day' => 'yesterday',
            $count === 1 && in_array($unit, ['month', 'year'], true) => "last {$unit}",
            default => "{$count} {$unit}".($count === 1 ? '' : 's').' ago',
        };
    }

    protected static function format(string $skeleton, DateTimeInterface $time, string $zone = self::ZONE): string
    {
        static $formatters = [];

        $formatters[$skeleton.$zone] ??= new IntlDateFormatter(
            'en_GB',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $zone,
            null,
            (new IntlDatePatternGenerator('en_GB'))->getBestPattern($skeleton),
        );

        return $formatters[$skeleton.$zone]->format($time);
    }
}
