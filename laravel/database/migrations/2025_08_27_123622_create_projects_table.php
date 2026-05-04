<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('project_title')->nullable();
            $table->longText('description')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->string('clientID')->nullable();
            $table->string('assign_by')->nullable();
            $table->longText('comments')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('projects');
    }
};
