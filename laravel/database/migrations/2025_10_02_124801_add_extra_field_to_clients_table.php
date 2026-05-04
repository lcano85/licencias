<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('clients', function (Blueprint $table) {
            $table->longText('licenseInformation')->nullable();
            $table->string('licenseAttachement')->nullable();
            $table->string('bankName')->nullable();
            $table->string('bankAccountNumber')->nullable();
            $table->string('bankCode')->nullable();
            $table->longText('documentRepository')->nullable();
        });
    }

    public function down(): void {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('licenseInformation');
            $table->dropColumn('licenseAttachement');
            $table->dropColumn('bankName');
            $table->dropColumn('bankAccountNumber');
            $table->dropColumn('bankCode');
            $table->dropColumn('documentRepository');
        });
    }
};
