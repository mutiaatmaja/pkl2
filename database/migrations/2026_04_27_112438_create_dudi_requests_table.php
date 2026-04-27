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
        Schema::create('dudi_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('name');
            $table->text('address');
            $table->string('panggilan_pimpinan', 50)->default('Pimpinan');
            $table->unsignedSmallInteger('kuota')->default(1);
            $table->string('status', 20)->default('pending');
            $table->text('admin_feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_dudi_id')->nullable()->constrained('dudis')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dudi_requests');
    }
};
