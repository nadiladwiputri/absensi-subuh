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
        Schema::create('santri', function (Blueprint $table) {
            $table->id('id_santri');
            $table->string('nama_santri', 100);
            $table->string('nis', 30)->unique();
            $table->integer('fingerprint_id')->nullable()->unique();
            $table->string('kelas', 30)->nullable();
            $table->string('no_wa_ortu', 20);
            $table->string('status', 20); // Aktif / Nonaktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri');
    }
};
