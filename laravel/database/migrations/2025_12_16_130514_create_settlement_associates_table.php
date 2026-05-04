<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('settlement_associates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('settlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('associate_id')->constrained('users');
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('fixed_amount', 15, 2)->nullable();
            $table->decimal('calculated_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('settlement_associates');
    }
};
