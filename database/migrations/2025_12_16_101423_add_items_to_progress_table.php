<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
    //     Schema::table('progress', function (Blueprint $table) {
            
    //         // 1. Cek apakah kolom 'material_id' BELUM ada? Kalau belum, baru buat.
    //         if (!Schema::hasColumn('progress', 'material_id')) {
    //             $table->foreignId('material_id')->nullable()->constrained()->onDelete('cascade');
    //         }

    //         // 2. Cek apakah kolom 'quiz_id' BELUM ada?
    //         if (!Schema::hasColumn('progress', 'quiz_id')) {
    //             $table->foreignId('quiz_id')->nullable()->constrained()->onDelete('cascade');
    //         }

    //         // 3. Cek apakah kolom 'is_completed' BELUM ada?
    //         if (!Schema::hasColumn('progress', 'is_completed')) {
    //             $table->boolean('is_completed')->default(false);
    //         }

    //         // 4. Tambahkan Unique Key (Bungkus try-catch agar tidak error jika key sudah ada)
    //         // Atau kita bisa cek index existence secara manual, tapi cara termudah di Laravel:
    //     });

    //     // Terpisah agar lebih aman menangani index unik
    //     try {
    //         Schema::table('progress', function (Blueprint $table) {
    //             $table->unique(['user_id', 'course_id', 'material_id', 'quiz_id'], 'unique_progress_item');
    //         });
    //     } catch (\Exception $e) {
    //         // Index mungkin sudah ada, kita abaikan error ini
    //     }
    }

    public function down()
    {
        // Schema::table('progress', function (Blueprint $table) {
        //     // Hapus foreign key dulu jika ada
        //     // Note: Nama foreign key default biasanya table_column_foreign
        //     $table->dropForeign(['material_id']);
        //     $table->dropForeign(['quiz_id']);
            
        //     // Hapus index unik
        //     $table->dropUnique('unique_progress_item');

        //     // Hapus kolom
        //     $table->dropColumn(['material_id', 'quiz_id', 'is_completed']);
        // });
    }
};