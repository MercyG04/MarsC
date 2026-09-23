<?php

namespace Database\Seeders;

use App\Enums\AddOnRateType;
use App\Models\AddOn;
use Illuminate\Database\Seeder;

class AddOnSeeder extends Seeder
{
    public function run(): void
    {
        $addOns = [
            [
                'name'        => 'Windshield Cover',
                'code'        => 'windshield',
                'rate_type'   => AddOnRateType::Fixed,
                'rate_value'  => 5000.00,
                'description' => 'Covers replacement of windscreen and window glass.',
            ],
            [
                'name'        => 'Excess Protector',
                'code'        => 'excess_protector',
                'rate_type'   => AddOnRateType::Percentage,
                'rate_value'  => 2.50,
                'description' => 'Reduces the policy excess payable on a claim.',
            ],
            [
                'name'        => 'Personal Accident',
                'code'        => 'personal_accident',
                'rate_type'   => AddOnRateType::Fixed,
                'rate_value'  => 3000.00,
                'description' => 'Compensation for driver/passenger injury or death.',
            ],
            [
                'name'        => 'Roadside Assistance',
                'code'        => 'roadside',
                'rate_type'   => AddOnRateType::Fixed,
                'rate_value'  => 2500.00,
                'description' => 'Towing, jump-start, fuel delivery, flat tyre support.',
            ],
            [
                'name'        => 'Radio & Entertainment Cover',
                'code'        => 'radio_entertainment',
                'rate_type'   => AddOnRateType::Fixed,
                'rate_value'  => 4000.00,
                'description' => 'Covers theft or damage to in-car entertainment systems.',
            ],
        ];

        foreach ($addOns as $addOn) {
            AddOn::updateOrCreate(
                ['code' => $addOn['code']],
                $addOn
            );
        }
    }
}