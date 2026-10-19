<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['status', 'name', 'email', 'phone', 'company', 'services', 'budget', 'message', 'received_at'])]
class Enquiry extends Model
{
    use HasUuids;

    // An enquiry is dated by when it arrived
    const CREATED_AT = 'received_at';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'new',
        'company' => '',
        'services' => '[]',
        'budget' => '',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'services' => 'array',
            'received_at' => 'datetime',
        ];
    }

    // The words the admin panel's search looks through
    public function searchText(): string
    {
        return mb_strtolower(implode(' ', [
            $this->name, $this->email, $this->phone, $this->company, $this->budget, $this->message, ...$this->services,
        ]));
    }
}
