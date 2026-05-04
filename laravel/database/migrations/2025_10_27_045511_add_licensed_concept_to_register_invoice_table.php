<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('register_invoice', function (Blueprint $table) {
            $table->string('paidPeriod')->after('periodPaid')->nullable();
            $table->string('licensedConcept')->after('paidPeriod')->nullable();
            $table->string('licensedEnvironment')->after('licensedConcept')->nullable();
            $table->string('commercialID')->after('licensedEnvironment')->nullable();
            $table->string('user_type')->after('commercialID')->nullable();
            $table->string('company')->after('user_type')->nullable();
            $table->string('commercialName')->after('company')->nullable();
            $table->string('subTotal')->after('commercialName')->nullable();
            $table->string('vat')->after('subTotal')->nullable();
            $table->string('total')->after('vat')->nullable();
        });
    }

    public function down(): void {
        Schema::table('register_invoice', function (Blueprint $table) {
            $table->string('paidPeriod')->nullable();
            $table->string('licensedConcept')->nullable();
            $table->string('licensedEnvironment')->nullable();
            $table->string('commercialID')->nullable();
            $table->string('user_type')->nullable();
            $table->string('company')->nullable();
            $table->string('commercialName')->nullable();
            $table->string('subTotal')->nullable();
            $table->string('vat')->nullable();
            $table->string('total')->nullable();
        });
    }
};
