<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('project_attachment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('projectID');
            $table->string('attachment_file')->nullable();
            $table->timestamps();

            $table->foreign('projectID')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_attachment');
    }
};
