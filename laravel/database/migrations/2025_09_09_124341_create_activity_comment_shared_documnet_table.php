<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('activity_comment_shared_documnet', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('activityID');
            $table->unsignedBigInteger('activityCommentID');
            $table->string('attachment_file')->nullable();
            $table->timestamps();

            $table->foreign('activityID')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('activityCommentID')->references('id')->on('activity_comment')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('activity_comment_shared_documnet');
    }
};
