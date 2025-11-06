<?php

namespace Database\Seeders;

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
            ['room_code' => 'Grade 1', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3158, 'longitude' => 123.8855],

            ['room_code' => '212', 'building_name' => 'Old Building', 'latitude' => 10.3160, 'longitude' => 123.8860],

            ['room_code' => 'A2-404', 'building_name' => 'Annex Building', 'latitude' => 10.3162, 'longitude' => 123.8865],

            ['room_code' => 'Controller 7', 'building_name' => 'Maritime Building', 'latitude' => 10.3165, 'longitude' => 123.8870],

            ['room_code' => '506', 'building_name' => 'CBE Building', 'latitude' => 10.3168, 'longitude' => 123.8875],

            ['room_code' => 'Field Area', 'building_name' => 'Field', 'latitude' => 10.3170, 'longitude' => 123.8880],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['room_code' => $room['room_code']], // unique identifier
                [
                    'building_name' => $room['building_name'],
                    'latitude' => $room['latitude'],
                    'longitude' => $room['longitude'],
                ]
            );
        }
    }
}
