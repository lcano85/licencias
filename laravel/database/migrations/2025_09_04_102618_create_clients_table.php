<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_acc_ID')->nullable();
            $table->string('commercialName')->nullable();
            $table->string('legalName')->nullable();
            $table->string('nit')->nullable();
            $table->string('categoryID')->nullable();
            $table->string('subcategoryID')->nullable();
            $table->string('useTypes')->nullable();
            $table->string('website_link')->nullable();
            $table->string('client_status')->nullable();
            $table->longText('annotations')->nullable();
            $table->longText('judicialNotificationAddress')->nullable();
            $table->longText('judicialNotification')->nullable();
            $table->string('companySize')->nullable();
            $table->string('companyType')->nullable();
            $table->string('annualIncome')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('clients');
    }
};
