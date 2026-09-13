<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 20);            // pix | cartao
            $table->string('status', 20);            // pendente | pago | recusado | expirado
            $table->decimal('amount', 10, 2);
            $table->unsignedTinyInteger('installments')->default(1);
            // Pix: o copia e cola e o prazo do QR.
            $table->text('pix_payload')->nullable();
            $table->timestamp('pix_expires_at')->nullable();
            // Cartão: só o que é seguro guardar.
            $table->string('card_brand', 20)->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->string('failure_reason', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
