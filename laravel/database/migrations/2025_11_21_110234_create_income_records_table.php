<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('income_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mode')->default('Transfer'); // Transfer or Deposit
            $table->string('bank_code')->default('112006');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('company')->nullable();
            $table->string('commercial_name')->nullable();
            $table->date('income_date');
            $table->decimal('income_amount', 15, 2);
            $table->decimal('other_amounts', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2);
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('concept')->nullable();
            $table->string('invoice_period')->nullable();
            $table->decimal('invoice_value', 15, 2)->nullable();
            $table->decimal('balance', 15, 2)->nullable();
            $table->string('rc_number')->nullable(); // Cash Receipt Number
            $table->date('rc_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('register_invoice')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('income_records');
    }
};
