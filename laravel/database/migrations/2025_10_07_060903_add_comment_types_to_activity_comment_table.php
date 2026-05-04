<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void {
        Schema::table('activity_comment', function (Blueprint $table) {
            $table->string('commentTypes')->nullable()->after('act_comment');
        });
    }

    public function down(): void {
        Schema::table('activity_comment', function (Blueprint $table) {
            $table->dropColumn('commentTypes');
        });
    }
};
