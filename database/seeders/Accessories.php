<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessoryHead;
use App\Models\Accessory;

class Accessories extends Seeder
{
    public function run()
    {
        // Create accessory_heads
        $accessoryHeads = [
            ['name' => 'Study Table', ],
            ['name' => 'Chair', ],
            ['name' => 'Almira', ],
            ['name' => 'Locker', ],
            ['name' => 'Welcome Kit', ],
            ['name' => 'Studt Lamp', ],
            ['name' => 'Bookshelf', ],
            ['name' => 'Mattress', ],
            ['name' => 'BedSheet', ],
            ['name' => 'Pillow', ],
        ];

        $headMap = [];

        foreach ($accessoryHeads as $head) {
            $created = AccessoryHead::firstOrCreate(['name' => $head['name']], $head);
            $headMap[$created->name] = $created->id;
        }

        // Accessories with price
        // $accessories = [
        //     ['name' => 'Single Bed', 'accessory_head_id' => $headMap['Furniture'], 'price' => 4500, 'is_default' => true, ],
        //     ['name' => 'Study Table', 'accessory_head_id' => $headMap['Furniture'], 'price' => 2500, 'is_default' => false, ],
        //     ['name' => 'Chair', 'accessory_head_id' => $headMap['Furniture'], 'price' => 0, 'is_default' => true, ],
        //     ['name' => 'Ceiling Fan', 'accessory_head_id' => $headMap['Electronics'], 'price' => 1800, 'is_default' => true, ],
        //     ['name' => 'LED Light', 'accessory_head_id' => $headMap['Electronics'], 'price' => 0, 'is_default' => true, ],
        //     ['name' => 'Iron', 'accessory_head_id' => $headMap['Electronics'], 'price' => 300, 'is_default' => true, ],
        //     ['name' => 'Cooler', 'accessory_head_id' => $headMap['Electronics'], 'price' => 1000, 'is_default' => false, ],
        //     ['name' => 'AC', 'accessory_head_id' => $headMap['Electronics'], 'price' => 2000, 'is_default' => false, ],
        //     ['name' => 'Mattress', 'accessory_head_id' => $headMap['Bedding'], 'price' => 2200, 'is_default' => true, ],
        //     ['name' => 'Pillow', 'accessory_head_id' => $headMap['Bedding'], 'price' => 200, 'is_default' => false, ],
        //     ['name' => 'Broom', 'accessory_head_id' => $headMap['Cleaning Supplies'], 'price' => 0, 'is_default' => false, ],
        //     ['name' => 'Dustbin', 'accessory_head_id' => $headMap['Cleaning Supplies'], 'price' => 0, 'is_default' => true, ],
        //     ['name' => 'Bucket', 'accessory_head_id' => $headMap['Utilities'], 'price' => 0, 'is_default' => true, ],
        //     ['name' => 'Mug', 'accessory_head_id' => $headMap['Utilities'], 'price' => 0, 'is_default' => true, ],

        // ];

        $accessories = [
            ['accessory_head_id' => $headMap['Study Table'], 'price' => 0, 'is_default' => true, 'is_active' => true,],
            ['accessory_head_id' => $headMap['Chair'], 'price' => 500, 'is_default' => false, 'is_active' => true,],
            ['accessory_head_id' => $headMap['Almira'], 'price' => 0, 'is_default' => true, 'is_active' => true,],
            ['accessory_head_id' => $headMap['Locker'], 'price' => 300, 'is_default' => true, 'is_active' => true,],
            ['accessory_head_id' => $headMap['Welcome Kit'], 'price' => 0, 'is_default' => true, 'is_active' => true,],
            ['accessory_head_id' => $headMap['Studt Lamp'], 'price' => 300, 'is_default' => false, 'is_active' => true,],
            ['accessory_head_id' => $headMap['Mattress'], 'price' => 1000, 'is_default' => false, 'is_active' => true,],
            ['accessory_head_id' => $headMap['Bookshelf'], 'price' => 500, 'is_default' => false, 'is_active' => true,],
            ['accessory_head_id' => $headMap['BedSheet'], 'price' => 500, 'is_default' => false, 'is_active' => true,],
            ['accessory_head_id' => $headMap['Pillow'], 'price' => 400, 'is_default' => false, 'is_active' => true,],

        ];

        foreach ($accessories as $item) {
            Accessory::where('accessory_head_id', $item['accessory_head_id'])->update(['is_active' => 0]);
            Accessory::create(array_merge($item, ['is_active' => 1]));
        }

         echo "Accessory Head and Accessorries created successfully! \n";
    }
}
