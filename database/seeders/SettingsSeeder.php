<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'hospital' => [
                'hospital_name' => 'City Care Hospital',
                'hospital_tagline' => 'Healing Hands, Caring Hearts',
                'hospital_address' => '123 Medical Square, Main Road',
                'hospital_city' => 'Bengaluru',
                'hospital_state' => 'Karnataka',
                'hospital_pincode' => '560001',
                'hospital_phone' => '+91 9876543210',
                'hospital_email' => 'contact@citycare.com',
                'hospital_website' => 'www.citycare.com',
            ],
            'system' => [
                'currency_symbol' => '₹',
                'currency_name' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'date_format' => 'd/m/Y',
                'financial_year_start' => '04-01',
                'uhid_prefix' => 'HMS-',
                'invoice_prefix' => 'INV-',
                'consultation_fee_default' => '500',
            ],
            'invoice' => [
                'invoice_header' => 'City Care Hospital - Final Bill',
                'invoice_footer' => 'Thank you for choosing City Care Hospital. Get well soon!',
                'show_tax' => 'true',
                'tax_percentage' => '18',
            ]
        ];

        foreach ($settings as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => $group]
                );
            }
        }
    }
}
