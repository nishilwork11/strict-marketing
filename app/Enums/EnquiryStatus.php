<?php

namespace App\Enums;

// Where an enquiry is in the sales process
enum EnquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    // One colour per status, checked to stay apart for colour-blind readers. New and
    // Contacted are light against white, so a status always shows its label too.
    public function color(): string
    {
        return match ($this) {
            self::New => 'bg-cyan',
            self::Contacted => 'bg-[#e3a200]',
            self::Won => 'bg-[#087a2a]',
            self::Lost => 'bg-grey',
        };
    }

    // The pale background of a status pill
    public function tint(): string
    {
        return match ($this) {
            self::New => 'bg-cyan/12',
            self::Contacted => 'bg-[#e3a200]/15',
            self::Won => 'bg-[#087a2a]/12',
            self::Lost => 'bg-mist',
        };
    }
}
