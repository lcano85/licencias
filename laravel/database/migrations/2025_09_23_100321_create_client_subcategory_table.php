<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('client_subcategory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('categoryID');
            $table->longText('subcategory_name');
            $table->string('subcategory_status');
            $table->timestamps();

            $table->foreign('categoryID')->references('id')->on('client_category')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_subcategory');
    }
};
