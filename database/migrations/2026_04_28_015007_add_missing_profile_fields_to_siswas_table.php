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
            if (! Schema::hasColumn('siswas', 'jenis_kelamin')) {
                $table->string('jenis_kelamin', 1)->nullable()->after('nisn');
            }

            if (! Schema::hasColumn('siswas', 'alamat')) {
                $table->text('alamat')->nullable()->after('jenis_kelamin');
            }

            if (! Schema::hasColumn('siswas', 'no_hp')) {
                $table->string('no_hp', 30)->nullable()->after('alamat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('siswas', 'no_hp')) {
                $dropColumns[] = 'no_hp';
            }

            if (Schema::hasColumn('siswas', 'alamat')) {
                $dropColumns[] = 'alamat';
            }

            if (Schema::hasColumn('siswas', 'jenis_kelamin')) {
                $dropColumns[] = 'jenis_kelamin';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
