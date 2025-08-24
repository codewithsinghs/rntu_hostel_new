<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessoryHead;

class AccessorySeeder extends Seeder
{
    public function run()
    {
        $accessories = [
             // Paid accessories
            ['name' => 'Locker',        'is_paid' => true,  'default_price' => 200, 'billing_cycle' => 'monthly',    'is_active' => true],
            ['name' => 'Study Table',   'is_paid' => true,  'default_price' => 500, 'billing_cycle' => 'quarterly',  'is_active' => true],
            ['name' => 'Bookshelf',     'is_paid' => true,  'default_price' => 300, 'billing_cycle' => 'bimonthly',  'is_active' => true],
            ['name' => 'Mini Fridge',   'is_paid' => true,  'default_price' => 1000,'billing_cycle' => 'one_time',   'is_active' => true],

            // Complementary accessories
            ['name' => 'Mattress',      'is_paid' => false, 'default_price' => 0,   'billing_cycle' => 'one_time',   'is_active' => true],
            ['name' => 'Chair',         'is_paid' => false, 'default_price' => 0,   'billing_cycle' => 'one_time',   'is_active' => true],
            ['name' => 'Curtains',      'is_paid' => false, 'default_price' => 0,   'billing_cycle' => 'one_time',   'is_active' => true],
            ['name' => 'Welcome Kit',   'is_paid' => false, 'default_price' => 0,   'billing_cycle' => 'one_time',   'is_active' => true],
        ];

        foreach ($accessories as $item) {
            AccessoryHead::create($item);
        }
        echo "Fee Accesory successfully! \n";
    }

}
