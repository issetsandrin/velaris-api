<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 60);
            $table->string('delivery_time', 60)->nullable(); // "5 a 8 dias úteis"
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('offers_free_shipping')->default(true);
            // Vazio: usa o limite padrão da loja, em Configurações.
            $table->decimal('free_from', 10, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('shipping_method_id')->nullable()->after('installments')->constrained()->nullOnDelete();
            $table->string('shipping_method_name', 60)->nullable()->after('shipping_method_id');
        });

        // A loja tinha um frete único nas configurações: vira a primeira forma de entrega.
        $valor = DB::table('settings')->where('key', 'frete_valor')->value('value');
        $valor = $valor !== null ? (float) $valor : (float) config('velaris.frete.valor');
        $agora = now();

        $id = DB::table('shipping_methods')->insertGetId([
            'code' => 'normal',
            'name' => 'Entrega normal',
            'delivery_time' => '5 a 8 dias úteis',
            'price' => $valor,
            'offers_free_shipping' => true,
            'free_from' => null,
            'active' => true,
            'position' => 1,
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);

        // Até aqui só existia esse frete, então os pedidos antigos são todos dele.
        DB::table('orders')->update(['shipping_method_id' => $id, 'shipping_method_name' => 'Entrega normal']);

        DB::table('settings')->where('key', 'frete_valor')->delete();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('shipping_method_id');
            $table->dropColumn('shipping_method_name');
        });

        Schema::dropIfExists('shipping_methods');
    }
};
