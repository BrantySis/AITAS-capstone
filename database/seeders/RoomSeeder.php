<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            // Basic Ed Building
            ['room_code' => '401', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3158, 'longitude' => 123.8855],
            ['room_code' => '402', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3159, 'longitude' => 123.8856],
            ['room_code' => '403', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3160, 'longitude' => 123.8857],
            ['room_code' => '404', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3161, 'longitude' => 123.8858],
            ['room_code' => '405', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3162, 'longitude' => 123.8859],

            // Old Building
            ['room_code' => '212', 'building_name' => 'Old Building', 'latitude' => 10.3160, 'longitude' => 123.8860],
            ['room_code' => '208', 'building_name' => 'Old Building', 'latitude' => 10.3161, 'longitude' => 123.8861],
            ['room_code' => '207', 'building_name' => 'Old Building', 'latitude' => 10.3162, 'longitude' => 123.8862],
            ['room_code' => 'A31', 'building_name' => 'Old Building', 'latitude' => 10.3163, 'longitude' => 123.8863],
            ['room_code' => 'A32', 'building_name' => 'Old Building', 'latitude' => 10.3164, 'longitude' => 123.8864],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['room_code' => $room['room_code']], // unique identifier
                [
                    'building_name' => $room['building_name'],
                    'latitude' => $room['latitude'],
                    'longitude' => $room['longitude'],
                    'is_active' => 1,
                ]
            );
        }
    }
}
