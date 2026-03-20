<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pair_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->string('driver', 30);
            $table->string('navigator', 30)->nullable();
            $table->enum('status', ['waiting', 'active', 'ended'])->default('waiting');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pair_sessions');
    }
};
