<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('settlements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('settlement_no')->unique();
            $table->string('origin')->nullable();
            $table->string('concept')->nullable();
            $table->date('income_month_start')->nullable();
            $table->date('income_month_end')->nullable();
            $table->string('period_covered')->nullable();
            $table->string('distribution_formula')->nullable();
            $table->decimal('total_to_distribute', 15, 2)->default(0);
            $table->decimal('amount_to_pay', 15, 2)->default(0);
            $table->enum('distribution_type', ['ownership', 'manual'])->default('ownership');
            $table->json('distribution_data')->nullable();
            $table->json('associates_data')->nullable();
            $table->enum('status', ['pending', 'settled', 'paid'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->foreignId('distribution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('settlements');
    }
};
