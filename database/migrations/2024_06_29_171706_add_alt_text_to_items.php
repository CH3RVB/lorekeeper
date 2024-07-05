<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('items', function (Blueprint $table) {
            $table->string('alt_text', 512)->nullable()->after('parsed_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('alt_text');
        });
    }
};
