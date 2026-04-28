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
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('jenis_kelamin', 1)->nullable()->after('nisn');
            $table->text('alamat')->nullable()->after('jenis_kelamin');
            $table->string('no_hp', 30)->nullable()->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'alamat', 'no_hp']);
        });
    }
};
