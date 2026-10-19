<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enquiries sent through the contact form, listed in the admin panel
        Schema::create('enquiries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status', 20)->default('new')->index();
            $table->string('name', 100);
            $table->string('email', 200);
            $table->string('phone', 20);
            $table->string('company', 120)->default('');
            $table->json('services');
            $table->string('budget', 40)->default('');
            $table->text('message');
            $table->timestamp('received_at')->useCurrent()->index();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
