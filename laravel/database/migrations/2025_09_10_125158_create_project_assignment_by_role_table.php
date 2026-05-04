<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('project_assignment_by_role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('role_id');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_assignment_by_role');
    }
};
