<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('clients', function (Blueprint $table) {
            $table->longText('personalContactData')->nullable();

            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('designation');
            $table->dropColumn('cnt_email');
            $table->dropColumn('cnt_phone_number');
        });
    }

    public function down(): void {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('personalContactData');
        });
    }
};
