<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Office 365 Business Standard',
                'slug' => 'office-365-business-standard',
                'description' => 'Office 365 Business Standard incluye aplicaciones de Office, correo electrónico empresarial y almacenamiento en la nube.',
                'type' => 'recurring',
                'duration_value' => 1,
                'duration_unit' => 'years',
                'price' => 120.00,
                'currency' => 'USD',
                'tax_enabled' => true,
                'tax_percentage' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Microsoft 365 Business Premium',
                'slug' => 'microsoft-365-business-premium',
                'description' => 'Microsoft 365 Business Premium con todas las características avanzadas de seguridad y gestión.',
                'type' => 'recurring',
                'duration_value' => 1,
                'duration_unit' => 'years',
                'price' => 180.00,
                'currency' => 'USD',
                'tax_enabled' => true,
                'tax_percentage' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Hosting Web Básico',
                'slug' => 'hosting-web-basico',
                'description' => 'Hosting web básico con 5GB de almacenamiento, dominio incluido y soporte técnico.',
                'type' => 'recurring',
                'duration_value' => 12,
                'duration_unit' => 'months',
                'price' => 120000.00,
                'currency' => 'COP',
                'tax_enabled' => true,
                'tax_percentage' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Hosting Web Premium',
                'slug' => 'hosting-web-premium',
                'description' => 'Hosting web premium con 20GB de almacenamiento, SSL gratuito y soporte prioritario.',
                'type' => 'recurring',
                'duration_value' => 12,
                'duration_unit' => 'months',
                'price' => 250000.00,
                'currency' => 'COP',
                'tax_enabled' => true,
                'tax_percentage' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Dominio .com',
                'slug' => 'dominio-com',
                'description' => 'Registro de dominio .com por un año.',
                'type' => 'recurring',
                'duration_value' => 1,
                'duration_unit' => 'years',
                'price' => 50000.00,
                'currency' => 'COP',
                'tax_enabled' => false,
                'tax_percentage' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Certificado SSL',
                'slug' => 'certificado-ssl',
                'description' => 'Certificado SSL para un dominio por un año.',
                'type' => 'recurring',
                'duration_value' => 1,
                'duration_unit' => 'years',
                'price' => 80000.00,
                'currency' => 'COP',
                'tax_enabled' => false,
                'tax_percentage' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Migración de Sitio Web',
                'slug' => 'migracion-sitio-web',
                'description' => 'Servicio único de migración completa de sitio web a nuevo servidor.',
                'type' => 'one_time',
                'duration_value' => null,
                'duration_unit' => null,
                'price' => 300000.00,
                'currency' => 'COP',
                'tax_enabled' => true,
                'tax_percentage' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Configuración Inicial',
                'slug' => 'configuracion-inicial',
                'description' => 'Servicio único de configuración inicial de servidor y aplicaciones.',
                'type' => 'one_time',
                'duration_value' => null,
                'duration_unit' => null,
                'price' => 500000.00,
                'currency' => 'COP',
                'tax_enabled' => true,
                'tax_percentage' => 19.00,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }
    }
}
