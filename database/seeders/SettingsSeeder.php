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
                'hospital_name' => 'Dwarakamai Children\'s Hospital',
                'hospital_tagline' => 'Healing Hands, Caring Hearts for Your Little Ones',
                'hospital_address' => 'Opp. Telangana Grameena Bank, Tilak Gardens, Khaleelwadi',
                'hospital_city' => 'Nizamabad',
                'hospital_state' => 'Telangana',
                'hospital_pincode' => '503003',
                'hospital_phone' => '080088 02006',
                'hospital_email' => 'contact@dwarakamai.com',
                'hospital_website' => 'www.dwarakamaihospital.com',
            ],
            'system' => [
                'currency_symbol' => '₹',
                'currency_name' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'date_format' => 'd/m/y',
                'financial_year_start' => '04-01',
                'uhid_prefix' => 'PAT-',
                'invoice_prefix' => 'INV-',
                'consultation_fee_default' => '500',
                'opd_validity_days' => '7',
            ],
            'invoice' => [
                'invoice_header' => 'Dwarakamai Children\'s Hospital - Medical Invoice',
                'invoice_footer' => 'Thank you for choosing Dwarakamai Children\'s Hospital. Wish your child a speedy recovery!',
                'show_tax' => 'false',
                'tax_percentage' => '0',
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
