<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('budget', function (Blueprint $table) {
            $table->string('licensedConcept')->nullable();
            $table->string('licensedEnvironment')->nullable();
            $table->string('budget_month')->nullable();
            $table->string('budget_year')->nullable();
        });
    }

    public function down(): void{
        Schema::table('budget', function (Blueprint $table) {
            $table->dropColumn('licensedConcept');
            $table->dropColumn('licensedEnvironment');
            $table->dropColumn('budget_month');
            $table->dropColumn('budget_year');
        });
    }
};
