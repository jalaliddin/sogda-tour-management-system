<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->enum('visa_type', ['tourist', 'business', 'transit', 'evisa', 'visa_on_arrival'])->default('tourist');
            $table->string('country_from')->nullable();
            $table->string('country_to')->nullable();
            $table->string('applicant_name');
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('nationality')->nullable();
            $table->date('application_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('issued_date')->nullable();
            $table->string('visa_number')->nullable();
            $table->decimal('cost_usd', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected', 'expired'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visas');
    }
};
