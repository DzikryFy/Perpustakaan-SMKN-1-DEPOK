<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('no_bos', 50)->nullable();
            $table->string('judul');
            $table->string('jenis_barang')->nullable()->default('Koleksi Umum');
            $table->string('kategori', 100);
            $table->string('penerbit', 100)->nullable();
            $table->integer('stok')->default(0);
            $table->integer('tahun_cetak')->nullable();
            $table->string('asal_perolehan', 50)->nullable()->default('BOS');
            $table->string('gambar')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
