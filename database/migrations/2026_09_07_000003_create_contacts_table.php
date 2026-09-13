<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('phone', 30);
            $table->string('whatsapp', 30)->nullable();
            $table->string('cpf', 11)->nullable(); // nulo só em contatos migrados; a API exige no cadastro
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('contact_id')->nullable()->after('address_id')->constrained()->nullOnDelete();
            $table->string('customer_cpf', 11)->nullable()->after('customer_whatsapp');
        });

        // Migra o telefone que ficava no cadastro do cliente para um contato padrão.
        $agora = now();
        DB::table('users')->whereNotNull('phone')->orderBy('id')->each(function (object $user) use ($agora): void {
            DB::table('contacts')->insert([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'whatsapp' => $user->whatsapp,
                'cpf' => null,
                'is_default' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        });

        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['phone', 'whatsapp']));
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('contact_id');
            $table->dropColumn('customer_cpf');
        });
        Schema::dropIfExists('contacts');
    }
};
