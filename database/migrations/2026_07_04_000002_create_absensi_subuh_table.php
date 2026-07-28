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
        Schema::create('absensi_subuh', function (Blueprint $table) {
            $table->id('id_absensi');
            $table->unsignedBigInteger('id_santri');
            $table->dateTime('waktu_absensi');
            $table->date('tanggal');
            $table->time('jadwal_subuh');
            $table->string('status_kehadiran', 20); // Hadir / Terlambat / Tidak Hadir
            $table->integer('poin');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Set up foreign key relation
            $table->foreign('id_santri')
                  ->references('id_santri')
                  ->on('santri')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_subuh');
    }
};
