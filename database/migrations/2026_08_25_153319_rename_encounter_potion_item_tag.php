<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameEncounterPotionItemTag extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        DB::table('item_tags')->where('tag', 'encounterpotion')->update(['tag' => 'encounter_potion']);
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        DB::table('item_tags')->where('tag', 'encounter_potion')->update(['tag' => 'encounterpotion']);
    }
}
