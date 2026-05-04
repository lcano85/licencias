<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('billing_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoiceID');
            $table->string('commercialID')->nullable();
            $table->string('user_type')->nullable();
            $table->string('company')->nullable();
            $table->string('commercialName')->nullable();
            $table->string('concept')->nullable();
            $table->string('licensedConcept')->nullable();
            $table->string('licensedEnvironment')->nullable();
            $table->string('invoiceNumber')->nullable();
            $table->dateTime('invoiceDate')->nullable();
            $table->string('periodPaid')->nullable();
            $table->string('paidPeriod')->nullable();
            $table->string('criterion')->nullable();
            $table->string('subTotal')->nullable();
            $table->string('vat')->nullable();
            $table->string('total')->nullable();
            $table->string('balance')->nullable();
            $table->string('supportingDocument')->nullable();
            $table->string('documentDetail')->nullable();
            $table->string('createdBy')->nullable();
            $table->timestamps();

            $table->foreign('invoiceID')->references('id')->on('register_invoice')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('billing_list');
    }
};
