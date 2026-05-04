<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('budget', function (Blueprint $table) {
            $table->tinyInteger('billing_frequency')->nullable()->after('status');
            $table->tinyInteger('begin_month')->nullable()->after('billing_frequency');
            $table->year('begin_year')->nullable()->after('begin_month');
            $table->tinyInteger('finish_month')->nullable()->after('begin_year');
            $table->year('finish_year')->nullable()->after('finish_month');
            $table->decimal('annual_value', 15, 2)->nullable()->after('finish_year');
            $table->integer('total_months')->nullable()->after('annual_value');
            $table->decimal('monthly_value', 15, 2)->nullable()->after('total_months');
            $table->string('license_pdf')->nullable()->after('monthly_value');
            $table->string('category')->nullable()->after('license_pdf');
            $table->string('subcategory')->nullable()->after('category');
        });
    }

    public function down(): void {
        Schema::table('budget', function (Blueprint $table) {
            $table->dropColumn([
                'billing_frequency',
                'begin_month',
                'begin_year',
                'finish_month',
                'finish_year',
                'annual_value',
                'total_months',
                'monthly_value',
                'license_pdf',
                'category',
                'subcategory'
            ]);
        });
    }
};
