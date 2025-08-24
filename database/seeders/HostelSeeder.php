<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hostel;

use App\Models\Building;
use App\Models\Room;
use App\Models\Bed;

class HostelSeeder extends Seeder
{
    public function run()
    {
        $universities = [1]; // Example university IDs
        $createdBy = [1, 2];
        foreach ($universities as $universityId) {
            for ($b = 1; $b <= 3; $b++) {
                $building = Building::create([
                    'name' => "Hostel Block $b",
                    'building_code' => "HB-$universityId-$b",
                    'university_id' => $universityId,
                    'status' => 'active',
                    'floors' => rand(3, 5),
                    'created_by' => rand(1, 2),
                ]);

                for ($floor = 1; $floor <= $building->floors; $floor++) {
                    for ($r = 1; $r <= rand(4, 6); $r++) {
                        $room = Room::create([
                            'room_number' => "F{$floor}R{$r}",
                            'building_id' => $building->id,
                            'floor_no' => $floor,
                            'status' => 'available',
                            'created_by' => rand(1, 2),
                        ]);

                        for ($bed = 1; $bed <= rand(2, 4); $bed++) {
                            Bed::create([
                                'bed_number' => "B{$bed}",
                                'room_id' => $room->id,
                                'status' => 'available',
                                'created_by' => rand(1, 2),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
