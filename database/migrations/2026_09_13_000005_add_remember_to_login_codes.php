<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_codes', function (Blueprint $table): void {
            // A escolha de "manter conectado" é feita no primeiro passo e só
            // vale token no segundo: viaja com o desafio.
            $table->boolean('remember')->default(false)->after('attempts');
        });
    }

    public function down(): void
    {
        Schema::table('login_codes', function (Blueprint $table): void {
            $table->dropColumn('remember');
        });
    }
};
