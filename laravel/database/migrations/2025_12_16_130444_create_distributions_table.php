<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('distributions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('distribution_no')->unique();
            $table->string('origin')->nullable();
            $table->string('concept')->nullable();
            $table->date('distribution_date');
            $table->string('invoice_no')->nullable();
            $table->string('rc_no')->nullable();
            $table->decimal('base_value', 15, 2)->default(0);
            $table->decimal('vat', 15, 2)->default(0);
            $table->decimal('associate_subtotal', 15, 2)->default(0);
            $table->decimal('admin_subtotal', 15, 2)->default(0);
            $table->decimal('admin_vat', 15, 2)->default(0);
            $table->decimal('admin_total', 15, 2)->default(0);
            $table->decimal('total_to_pay', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('status', ['pending', 'distributed', 'settled', 'paid'])->default('pending');
            $table->json('metadata')->nullable();
            $table->foreignId('validation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('income_id')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('distributions');
    }
};
