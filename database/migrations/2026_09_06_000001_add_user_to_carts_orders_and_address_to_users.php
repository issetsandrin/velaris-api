<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('postal_code', 9)->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('street_number', 20)->nullable();
            $table->string('complement')->nullable();
        });

        Schema::table('carts', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('carts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['postal_code', 'city', 'street', 'street_number', 'complement']);
        });
    }
};
