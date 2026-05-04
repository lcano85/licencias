<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('cn_number')->unique();
            $table->date('cn_date');
            $table->string('reason')->nullable();
            $table->decimal('subTotal', 14, 2)->default(0);
            $table->decimal('vat', 5, 2)->default(0); // percent
            $table->decimal('total', 14, 2)->default(0);
            $table->string('supporting_doc')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('register_invoice')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('credit_notes');
    }
};
