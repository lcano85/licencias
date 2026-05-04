<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('cash_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('receipt_no')->index();
            $table->date('receipt_date');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('register_invoice')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cash_receipts');
    }
};
