<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('budget_criterion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('criterion_name')->nullable();
            $table->string('criterion_status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('budget_criterion');
    }
};
