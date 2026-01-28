<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Bold',
                'slug' => 'bold',
                'type' => 'automatic',
                'provider' => 'bold',
                'is_active' => true,
                'is_default' => true,
                'description' => 'Pasarela de pago Bold para pagos automáticos',
                'fee_percentage' => 0.00,
                'fee_fixed' => 0.00,
                'accepted_currencies' => ['COP', 'USD'],
                'requires_approval' => false,
                'auto_approve' => true,
                'configuration' => [
                    'api_key' => '',
                    'api_secret' => '',
                ],
            ],
            [
                'name' => 'Transferencia Bancaria',
                'slug' => 'bank_transfer',
                'type' => 'manual',
                'provider' => null,
                'is_active' => true,
                'is_default' => false,
                'description' => 'Pago mediante transferencia bancaria',
                'instructions' => 'Realiza la transferencia a la cuenta bancaria indicada y sube el comprobante.',
                'fee_percentage' => 0.00,
                'fee_fixed' => 0.00,
                'accepted_currencies' => ['COP'],
                'requires_approval' => true,
                'auto_approve' => false,
            ],
            [
                'name' => 'Efectivo',
                'slug' => 'cash',
                'type' => 'manual',
                'provider' => null,
                'is_active' => true,
                'is_default' => false,
                'description' => 'Pago en efectivo',
                'instructions' => 'Realiza el pago en efectivo y sube el comprobante correspondiente.',
                'fee_percentage' => 0.00,
                'fee_fixed' => 0.00,
                'accepted_currencies' => ['COP'],
                'requires_approval' => true,
                'auto_approve' => false,
            ],
            [
                'name' => 'PayU',
                'slug' => 'payu',
                'type' => 'gateway',
                'provider' => 'payu',
                'is_active' => false,
                'is_default' => false,
                'description' => 'Pasarela de pago PayU',
                'fee_percentage' => 3.50,
                'fee_fixed' => 0.00,
                'accepted_currencies' => ['COP'],
                'requires_approval' => false,
                'auto_approve' => true,
                'configuration' => [
                    'merchant_id' => '',
                    'api_key' => '',
                    'api_login' => '',
                ],
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['slug' => $method['slug']],
                $method
            );
        }
    }
}
