<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('validation_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('validation_id')->constrained('validations')->cascadeOnDelete();
            $table->enum('item_type', ['budget', 'invoice', 'income']);
            $table->unsignedBigInteger('item_id');
            $table->string('concept')->nullable();
            
            // Amounts
            $table->decimal('original_amount', 15, 2);
            $table->decimal('validated_amount', 15, 2)->nullable();
            
            // Accountant validation
            $table->enum('accountant_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('accountant_notes')->nullable();
            
            // Management validation
            $table->enum('management_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('management_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['validation_id', 'item_type', 'item_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('validation_items');
    }
};
