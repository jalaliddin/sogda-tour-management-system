<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counterparty_id')->nullable()->constrained('counterparties')->nullOnDelete();
            $table->enum('offer_type', ['inbound', 'outbound', 'package', 'custom'])->default('inbound');
            $table->string('offer_name');
            $table->json('destination_countries')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('pax_min')->default(1);
            $table->integer('pax_max')->default(100);
            $table->decimal('price_per_person_usd', 10, 2)->default(0);
            $table->decimal('total_price_usd', 12, 2)->default(0);
            $table->enum('currency', ['USD', 'EUR', 'UZS', 'RUB'])->default('USD');
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->longText('itinerary')->nullable();
            $table->date('validity_date')->nullable();
            $table->enum('status', ['new', 'reviewing', 'accepted', 'rejected', 'archived'])->default('new');
            $table->text('notes')->nullable();
            $table->date('received_date')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
