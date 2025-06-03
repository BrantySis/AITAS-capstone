<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            ['room_code' => 'Room 101', 'building_name' => 'Building A', 'latitude' => 10.3157, 'longitude' => 123.8854],
            ['room_code' => 'Room 102', 'building_name' => 'Building A', 'latitude' => 10.3158, 'longitude' => 123.8855],
            ['room_code' => 'Room 201', 'building_name' => 'Building B', 'latitude' => 10.3160, 'longitude' => 123.8860],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
