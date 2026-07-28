<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->renameColumn('no_wa_ortu', 'no_hp_ortu');
        });
        Schema::table('wali', function (Blueprint $table) {
            $table->renameColumn('no_wa', 'no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->renameColumn('no_hp_ortu', 'no_wa_ortu');
        });
        Schema::table('wali', function (Blueprint $table) {
            $table->renameColumn('no_hp', 'no_wa');
        });
    }
};
