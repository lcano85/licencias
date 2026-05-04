<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('licenses_agreements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('commercialID')->nullable();
            $table->string('commercialName')->nullable();
            $table->string('userType')->nullable();
            $table->longText('licensedConcept')->nullable();
            $table->longText('licensedEnvironment')->nullable();
            $table->dateTime('startDate')->nullable();
            $table->dateTime('endDate')->nullable();
            $table->string('monthlyValue')->nullable();
            $table->string('annualValue')->nullable();
            $table->string('status')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('licenses_agreements');
    }
};
