<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('budget', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('commercialID')->nullable();
            $table->string('user_type')->nullable();
            $table->string('company')->nullable();
            $table->string('commercialName')->nullable();
            $table->string('concept')->nullable();
            $table->string('subTotal')->nullable();
            $table->string('vat')->nullable();
            $table->string('total')->nullable();
            $table->string('condition')->nullable();
            $table->string('status')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('budget');
    }
};
