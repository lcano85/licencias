<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('invoice_consecutive', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_consecutive', 'next_number')) {
                $table->unsignedBigInteger('next_number')->default(1)->after('consecutive_name');
            }
        });
    }

    public function down(): void {
        Schema::table('invoice_consecutive', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_consecutive', 'next_number')) {
                $table->dropColumn('next_number');
            }
        });
    }
};

