<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('receipt_consecutive', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('consecutive_name');
            $table->unsignedBigInteger('next_number')->default(1);
            $table->string('status')->default('1');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('receipt_consecutive');
    }
};

