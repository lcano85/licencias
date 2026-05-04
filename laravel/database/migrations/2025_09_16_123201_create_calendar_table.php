<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('calendar', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('schedule_title')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('start')->nullable(); 
            $table->dateTime('end')->nullable();
            $table->longText('location')->nullable();
            $table->longText('description')->nullable();
            $table->string('creator')->nullable();
            $table->string('guests')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('calendar');
    }
};
