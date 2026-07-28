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
        Schema::table('wali', function (Blueprint $table) {
            $table->string('telegram_chat_id', 50)->nullable()->after('no_wa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wali', function (Blueprint $table) {
            $table->dropColumn('telegram_chat_id');
        });
    }
};
