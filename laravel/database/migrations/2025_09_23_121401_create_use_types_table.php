<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('use_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('use_types_name');
            $table->string('use_types_status');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('use_types');
    }
};
