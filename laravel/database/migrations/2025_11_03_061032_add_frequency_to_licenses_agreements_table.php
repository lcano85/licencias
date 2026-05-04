<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('licenses_agreements', function (Blueprint $table) {
            $table->string('category')->after('licensedEnvironment')->nullable();
            $table->string('subcategory')->after('category')->nullable();
            $table->string('frequency')->after('subcategory')->nullable();
        });
    }

    public function down(): void {
        Schema::table('licenses_agreements', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('subcategory');
            $table->dropColumn('frequency');
        });
    }
};
