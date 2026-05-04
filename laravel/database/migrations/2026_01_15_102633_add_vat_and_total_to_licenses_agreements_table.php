<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('licenses_agreements', function (Blueprint $table) {
            $table->tinyInteger('vat')->nullable()->after('monthlyValue');
            $table->string('month_total_value')->nullable()->after('vat');
        });
    }

    public function down(): void {
        Schema::table('licenses_agreements', function (Blueprint $table) {
            $table->dropColumn([
                'vat',
                'month_total_value'
            ]);
        });
    }
};
