<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40)->nullable();
            $table->string('postal_code', 9);
            $table->string('city');
            $table->string('street');
            $table->string('street_number', 20);
            $table->string('complement')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('address_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        // Migra o endereço único que ficava no cadastro do cliente.
        $agora = now();
        DB::table('users')
            ->whereNotNull('postal_code')
            ->whereNotNull('street')
            ->orderBy('id')
            ->each(function (object $user) use ($agora): void {
                DB::table('addresses')->insert([
                    'user_id' => $user->id,
                    'label' => 'Principal',
                    'postal_code' => $user->postal_code,
                    'city' => $user->city,
                    'street' => $user->street,
                    'street_number' => $user->street_number,
                    'complement' => $user->complement,
                    'is_default' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['postal_code', 'city', 'street', 'street_number', 'complement']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('postal_code', 9)->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('street_number', 20)->nullable();
            $table->string('complement')->nullable();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('address_id');
        });

        Schema::dropIfExists('addresses');
    }
};
