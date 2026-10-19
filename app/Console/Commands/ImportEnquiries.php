<?php

namespace App\Console\Commands;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('enquiries:import {file : An enquiries.json file saved by the Next.js version of the site}')]
#[Description('Import enquiries from the Next.js site into the database, skipping any already here')]
class ImportEnquiries extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $file = $this->argument('file');
        $enquiries = is_file($file) ? json_decode(file_get_contents($file), true) : null;

        if (! is_array($enquiries)) {
            $this->components->error("[{$file}] isn't a readable enquiries file.");

            return self::FAILURE;
        }

        $imported = 0;
        foreach ($enquiries as $enquiry) {
            if (Enquiry::whereKey($enquiry['id'])->exists()) {
                continue;
            }

            $model = new Enquiry([
                'status' => EnquiryStatus::tryFrom($enquiry['status'] ?? '') ?? EnquiryStatus::New,
                'name' => $enquiry['name'],
                'email' => $enquiry['email'],
                'phone' => $enquiry['phone'] ?? '',
                'company' => $enquiry['company'] ?? '',
                'services' => $enquiry['services'] ?? [],
                'budget' => $enquiry['budget'] ?? '',
                'message' => $enquiry['message'],
                'received_at' => Carbon::parse($enquiry['receivedAt']),
            ]);
            // Keeps the id, so links to an enquiry still work
            $model->id = $enquiry['id'];
            $model->save();
            $imported++;
        }

        $this->components->info("Imported {$imported} of ".count($enquiries).' enquiries.');

        return self::SUCCESS;
    }
}
