<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::firstOrCreate(['code' => 'pix'], [
            'name' => 'Pix', 'type' => 'pix', 'description' => 'Aprovação na hora', 'discount_percent' => 5, 'max_installments' => 1, 'active' => true, 'position' => 1,
        ]);
        PaymentMethod::firstOrCreate(['code' => 'cartao'], [
            'name' => 'Cartão de crédito', 'type' => 'cartao', 'discount_percent' => 0, 'max_installments' => 3, 'min_installment_value' => 30, 'active' => true, 'position' => 2,
        ]);
    }
}
