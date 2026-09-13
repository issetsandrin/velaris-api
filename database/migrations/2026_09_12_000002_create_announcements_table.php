<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Avisos escritos no painel: promoções e recados da loja.
        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 120);
            $table->string('body', 400);
            $table->string('type', 20)->default('aviso'); // promocao | entrega | pagamento | aviso
            $table->string('link', 200)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Um registro por aviso lido, para o indicador zerar em qualquer aparelho.
        Schema::create('notification_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key', 120);
            $table->timestamp('read_at');
            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
        Schema::dropIfExists('announcements');
    }
};
