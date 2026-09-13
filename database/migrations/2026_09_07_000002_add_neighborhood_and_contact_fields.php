<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->string('neighborhood', 120)->nullable()->after('city');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('neighborhood', 120)->nullable()->after('city');
            $table->string('customer_phone', 30)->nullable()->after('customer_email');
            $table->string('customer_whatsapp', 30)->nullable()->after('customer_phone');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['phone', 'whatsapp']));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['neighborhood', 'customer_phone', 'customer_whatsapp']));
        Schema::table('addresses', fn (Blueprint $table) => $table->dropColumn('neighborhood'));
    }
};
