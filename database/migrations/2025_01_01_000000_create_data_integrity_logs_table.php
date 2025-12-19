<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_integrity_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedBigInteger('record_count')->default(0);
            $table->string('checksum', 128);
            $table->string('chain_hash', 128)->nullable();
            $table->timestamp('sealed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_integrity_logs');
    }
};
