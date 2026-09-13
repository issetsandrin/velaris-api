<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 60);
            $table->string('type', 20); // pix | cartao | boleto | outro
            $table->string('description', 120)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->unsignedTinyInteger('max_installments')->default(1);
            $table->decimal('min_installment_value', 10, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('payment_method_id')->nullable()->after('payment_method')->constrained()->nullOnDelete();
            $table->string('payment_method_name', 60)->nullable()->after('payment_method_id');
            $table->unsignedTinyInteger('installments')->default(1)->after('payment_method_name');
        });

        // Formas iniciais, herdando o desconto do Pix que ficava nas configurações.
        $descontoPix = DB::table('settings')->where('key', 'desconto_pix')->value('value');
        $descontoPix = $descontoPix !== null ? round((float) $descontoPix * 100, 2) : 5;
        $agora = now();

        DB::table('payment_methods')->insert([
            ['code' => 'pix', 'name' => 'Pix', 'type' => 'pix', 'description' => 'Aprovação na hora', 'discount_percent' => $descontoPix, 'max_installments' => 1, 'min_installment_value' => null, 'active' => true, 'position' => 1, 'created_at' => $agora, 'updated_at' => $agora],
            ['code' => 'cartao', 'name' => 'Cartão de crédito', 'type' => 'cartao', 'description' => null, 'discount_percent' => 0, 'max_installments' => 3, 'min_installment_value' => 30, 'active' => true, 'position' => 2, 'created_at' => $agora, 'updated_at' => $agora],
        ]);

        DB::table('orders')->where('payment_method', 'pix')->update(['payment_method_id' => DB::table('payment_methods')->where('code', 'pix')->value('id'), 'payment_method_name' => 'Pix']);
        DB::table('orders')->where('payment_method', 'cartao')->update(['payment_method_id' => DB::table('payment_methods')->where('code', 'cartao')->value('id'), 'payment_method_name' => 'Cartão de crédito']);

        DB::table('settings')->where('key', 'desconto_pix')->delete();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropColumn(['payment_method_name', 'installments']);
        });
        Schema::dropIfExists('payment_methods');
    }
};
