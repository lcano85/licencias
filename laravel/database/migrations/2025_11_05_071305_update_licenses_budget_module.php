<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for License & Budget module updates
     */
    public function up(): void
    {
        // Update licenses_agreements table
        Schema::table('licenses_agreements', function (Blueprint $table) {
            // Add new origin field
            if (!Schema::hasColumn('licenses_agreements', 'origin')) {
                $table->enum('origin', ['License', 'Transaction', 'Conciliation', 'Sentences'])
                      ->nullable()
                      ->after('subcategory')
                      ->comment('Origin of the license');
            }
            
            // Rename frequency to billing_frequency if it exists
            if (Schema::hasColumn('licenses_agreements', 'frequency') && 
                !Schema::hasColumn('licenses_agreements', 'billing_frequency')) {
                $table->renameColumn('frequency', 'billing_frequency');
            }
            
            // Add billing_frequency if it doesn't exist
            if (!Schema::hasColumn('licenses_agreements', 'billing_frequency')) {
                $table->string('billing_frequency', 50)
                      ->nullable()
                      ->after('endDate')
                      ->comment('Budget billing frequency (Monthly/Quarterly/Annual)');
            }
            
            // Add budget date fields
            if (!Schema::hasColumn('licenses_agreements', 'begin_month')) {
                $table->integer('begin_month')->nullable()->after('billing_frequency');
            }
            if (!Schema::hasColumn('licenses_agreements', 'begin_year')) {
                $table->integer('begin_year')->nullable()->after('begin_month');
            }
            if (!Schema::hasColumn('licenses_agreements', 'finish_month')) {
                $table->integer('finish_month')->nullable()->after('begin_year');
            }
            if (!Schema::hasColumn('licenses_agreements', 'finish_year')) {
                $table->integer('finish_year')->nullable()->after('finish_month');
            }
            
            // Modify licensedEnvironment to support JSON (multipick)
            // Note: You may need to manually convert existing data
            if (Schema::hasColumn('licenses_agreements', 'licensedEnvironment')) {
                $table->json('licensedEnvironment')
                      ->nullable()
                      ->change()
                      ->comment('Multiple environments can be selected');
            }
        });

        // Update budget table
        Schema::table('budget', function (Blueprint $table) {
            // Rename frequency to billing_frequency if it exists
            if (Schema::hasColumn('budget', 'frequency') && 
                !Schema::hasColumn('budget', 'billing_frequency')) {
                $table->renameColumn('frequency', 'billing_frequency');
            }
            
            // Add billing_frequency if it doesn't exist
            if (!Schema::hasColumn('budget', 'billing_frequency')) {
                $table->string('billing_frequency', 50)
                      ->nullable()
                      ->after('licensedEnvironment')
                      ->comment('Billing frequency for budget');
            }
            
            // Add budget display filter fields
            if (!Schema::hasColumn('budget', 'budget_month')) {
                $table->integer('budget_month')->nullable()->after('billing_frequency');
            }
            if (!Schema::hasColumn('budget', 'budget_year')) {
                $table->integer('budget_year')->nullable()->after('budget_month');
            }
            
            // Ensure other fields exist
            if (!Schema::hasColumn('budget', 'begin_month')) {
                $table->integer('begin_month')->nullable()->after('budget_year');
            }
            if (!Schema::hasColumn('budget', 'begin_year')) {
                $table->integer('begin_year')->nullable()->after('begin_month');
            }
            if (!Schema::hasColumn('budget', 'finish_month')) {
                $table->integer('finish_month')->nullable()->after('begin_year');
            }
            if (!Schema::hasColumn('budget', 'finish_year')) {
                $table->integer('finish_year')->nullable()->after('finish_month');
            }
            if (!Schema::hasColumn('budget', 'annual_value')) {
                $table->decimal('annual_value', 15, 2)->nullable()->after('finish_year');
            }
            if (!Schema::hasColumn('budget', 'total_months')) {
                $table->integer('total_months')->nullable()->after('annual_value');
            }
            if (!Schema::hasColumn('budget', 'monthly_value')) {
                $table->decimal('monthly_value', 15, 2)->nullable()->after('total_months');
            }
            if (!Schema::hasColumn('budget', 'license_pdf_path')) {
                $table->string('license_pdf_path')->nullable()->after('monthly_value');
            }
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('licenses_agreements', function (Blueprint $table) {
            $table->dropColumn([
                'origin',
                'begin_month',
                'begin_year', 
                'finish_month',
                'finish_year'
            ]);
            
            if (Schema::hasColumn('licenses_agreements', 'billing_frequency')) {
                $table->renameColumn('billing_frequency', 'frequency');
            }
        });

        Schema::table('budget', function (Blueprint $table) {
            $table->dropColumn([
                'budget_month',
                'budget_year'
            ]);
            
            if (Schema::hasColumn('budget', 'billing_frequency')) {
                $table->renameColumn('billing_frequency', 'frequency');
            }
        });
    }
};