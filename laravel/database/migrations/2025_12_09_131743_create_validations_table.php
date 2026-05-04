<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('validations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('report_type', ['billing', 'income']);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('title')->nullable();
            $table->json('concepts_data')->nullable();
            
            // Accountant validation
            $table->foreignId('accountant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('accountant_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('accountant_notes')->nullable();
            $table->timestamp('accountant_validated_at')->nullable();
            
            // Management validation
            $table->foreignId('management_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('management_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('management_notes')->nullable();
            $table->timestamp('management_validated_at')->nullable();
            
            // Metadata
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            
            $table->index(['report_type', 'period_start', 'period_end']);
            $table->index(['accountant_status', 'management_status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('validations');
    }
};
