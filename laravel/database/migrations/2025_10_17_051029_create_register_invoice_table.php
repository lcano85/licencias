<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('register_invoice', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('budgetID');
            $table->string('invoiceNumber')->nullable();
            $table->dateTime('invoiceDate')->nullable();
            $table->string('invoiceConsecutive')->nullable();
            $table->string('periodPaid')->nullable();
            $table->string('criterion')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->foreign('budgetID')->references('id')->on('budget')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('register_invoice');
    }
};
