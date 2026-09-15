<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_verifications', function (Blueprint $table): void {
            // O token usado deixa de ser apagado: abrir o link de novo precisa
            // dizer "já confirmado", e não "link inválido".
            $table->timestamp('used_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_verifications', function (Blueprint $table): void {
            $table->dropColumn('used_at');
        });
    }
};
