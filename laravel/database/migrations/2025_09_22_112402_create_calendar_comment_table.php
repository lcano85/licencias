<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('calendar_comment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('calendarID');
            $table->unsignedBigInteger('user_id');
            $table->longText('act_comment');
            $table->timestamps();

            $table->foreign('calendarID')->references('id')->on('calendar')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('calendar_comment');
    }
};
