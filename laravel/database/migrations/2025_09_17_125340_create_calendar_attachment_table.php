<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('calendar_attachment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('calendarID');
            $table->string('attachment_file')->nullable();
            $table->timestamps();

            $table->foreign('calendarID')->references('id')->on('calendar')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('calendar_attachment');
    }
};
