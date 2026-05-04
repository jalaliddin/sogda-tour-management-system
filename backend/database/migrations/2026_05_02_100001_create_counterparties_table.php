<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counterparties', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->enum('type', ['foreign_tour', 'local_tour', 'hotel', 'restaurant', 'guide', 'folklore', 'transport']);
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('contract_number')->nullable();
            $table->date('contract_date')->nullable();
            $table->date('contract_expiry')->nullable();
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->enum('currency', ['USD', 'EUR', 'UZS', 'RUB'])->default('USD');
            $table->json('bank_details')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('rating')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('documents')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counterparties');
    }
};
