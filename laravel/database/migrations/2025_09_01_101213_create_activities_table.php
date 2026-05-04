<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('activity_name')->nullable();
            $table->string('activity_type')->nullable();
            $table->longText('short_description')->nullable();
            $table->longText('main_description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('clientID')->nullable();
            $table->string('assign_by')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->longText('comments')->nullable();
            $table->string('status')->nullable();
            $table->string('request_accept_extension')->nullable();
            $table->string('complete_activity')->nullable();
            $table->string('discard_activity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('activities');
    }
};
