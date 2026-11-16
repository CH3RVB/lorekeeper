<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('limits', function (Blueprint $table) {
            $table->string('recipient_type')->default('User');
            $table->json('data')->nullable()->default(null);
        });

        Schema::table('user_unlocked_limits', function (Blueprint $table) {
            $table->json('data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('limits', function (Blueprint $table) {
            $table->dropColumn('recipient_type');
            $table->dropColumn('data');
        });
        Schema::table('user_unlocked_limits', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
};
