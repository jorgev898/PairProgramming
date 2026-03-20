<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pair_sessions', function (Blueprint $table) {
            // ID del thread de LangGraph — compartido entre ambos dispositivos
            $table->string('thread_id')->nullable()->after('status');
            // Historial de mensajes en JSON para persistir entre recargas
            $table->json('chat_history')->nullable()->after('thread_id');
        });
    }

    public function down(): void
    {
        Schema::table('pair_sessions', function (Blueprint $table) {
            $table->dropColumn(['thread_id', 'chat_history']);
        });
    }
};
