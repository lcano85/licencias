<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('portfolio_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('register_invoice')->onDelete('cascade');
            $table->string('period_month', 2); // MM
            $table->string('period_year', 4);  // YYYY
            $table->text('comment');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Unique constraint: one comment per client per period per invoice
            $table->unique(['client_id', 'invoice_id', 'period_month', 'period_year'], 'unique_portfolio_comment');
            
            // Index for faster queries
            $table->index(['period_month', 'period_year']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_comments');
    }
};
