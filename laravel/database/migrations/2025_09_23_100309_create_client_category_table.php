<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('client_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('category_name');
            $table->string('category_status');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_category');
    }
};
