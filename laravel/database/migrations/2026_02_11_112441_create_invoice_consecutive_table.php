<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('invoice_consecutive', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('consecutive_name');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoice_consecutive');
    }
};
