<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('license_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('license_id');
            $table->unsignedBigInteger('user_id');
            $table->longText('lic_comment');
            $table->timestamps();

            $table->foreign('license_id')->references('id')->on('licenses_agreements')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('license_comments');
    }
};
