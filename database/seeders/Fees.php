<?php

namespace Database\Seeders;

use App\Models\Fee;
use App\Models\User;
use App\Models\FeeHead;
use App\Models\University;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class Fees extends Seeder
{
    public function run()
    {
        // Create fee_heads
        $feeHeads = [
            ['name' => 'Hostel Fee'],
            ['name' => 'Mess Fee'],
            ['name' => 'Other'],
            ['name' => 'Caution Money'],
        ];

        $feeHeadMap = [];

        foreach ($feeHeads as $head) {
            $created = FeeHead::create($head);
            $feeHeadMap[$created->name] = $created->id;
        }

        // Create fees
        $fees = [
            ['name' => 'Hostel Fee', 'fee_head_id' => $feeHeadMap['Hostel Fee'], 'amount' => 6000.00],
            ['name' => 'Mess Fee', 'fee_head_id' => $feeHeadMap['Mess Fee'], 'amount' => 3000.00],
            ['name' => 'Caution Money', 'fee_head_id' => $feeHeadMap['Caution Money'], 'amount' => 10000.00],
            ['name' => 'Other', 'fee_head_id' => $feeHeadMap['Other'], 'amount' => 0.00],
            ['name' => 'Hostel Fee', 'fee_head_id' => $feeHeadMap['Hostel Fee'], 'amount' => 3000.00],
        ];

        // foreach ($fees as $fee) {
        //     Fee::create($fee);
        // }

        foreach ($fees as $feeData) {
            // Mark previous fees with same name as inactive
            Fee::where('name', $feeData['name'])->update(['is_active' => 0]);

            // Create new fee as active
            Fee::create(array_merge($feeData, ['is_active' => 1]));
        }

        echo "Fee Head and Fee created successfully! \n";
    }
}
