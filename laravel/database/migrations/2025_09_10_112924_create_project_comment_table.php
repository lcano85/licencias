<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('project_comment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('projectID');
            $table->unsignedBigInteger('user_id');
            $table->longText('prj_comment');
            $table->timestamps();

            $table->foreign('projectID')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_comment');
    }
};
