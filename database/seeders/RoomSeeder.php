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
            ['room_code' => 'Grade 1', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3158, 'longitude' => 123.8855],
            ['room_code' => 'Grade 2', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3159, 'longitude' => 123.8856],
            ['room_code' => 'Grade 3', 'building_name' => 'Basic Ed Building', 'latitude' => 10.3160, 'longitude' => 123.8857],

            // Old Building
            ['room_code' => '211', 'building_name' => 'Old Building', 'latitude' => 10.3160, 'longitude' => 123.8860],
            ['room_code' => '212', 'building_name' => 'Old Building', 'latitude' => 10.3161, 'longitude' => 123.8861],
            ['room_code' => '213', 'building_name' => 'Old Building', 'latitude' => 10.3162, 'longitude' => 123.8862],

            // Annex Building
            ['room_code' => 'A2-401', 'building_name' => 'Annex Building', 'latitude' => 10.3162, 'longitude' => 123.8865],
            ['room_code' => 'A2-402', 'building_name' => 'Annex Building', 'latitude' => 10.3163, 'longitude' => 123.8866],
            ['room_code' => 'A2-403', 'building_name' => 'Annex Building', 'latitude' => 10.3164, 'longitude' => 123.8867],

            // Maritime Building
            ['room_code' => 'Control 5', 'building_name' => 'Maritime Building', 'latitude' => 10.3165, 'longitude' => 123.8870],
            ['room_code' => 'Control 6', 'building_name' => 'Maritime Building', 'latitude' => 10.3166, 'longitude' => 123.8871],
            ['room_code' => 'Control 7', 'building_name' => 'Maritime Building', 'latitude' => 10.3167, 'longitude' => 123.8872],

            // CBE Building
            ['room_code' => '504', 'building_name' => 'CBE Building', 'latitude' => 10.3168, 'longitude' => 123.8875],
            ['room_code' => '505', 'building_name' => 'CBE Building', 'latitude' => 10.3169, 'longitude' => 123.8876],
            ['room_code' => '506', 'building_name' => 'CBE Building', 'latitude' => 10.3170, 'longitude' => 123.8877],

            // Field
            ['room_code' => 'Field Area 1', 'building_name' => 'Field', 'latitude' => 10.3170, 'longitude' => 123.8880],
            ['room_code' => 'Field Area 2', 'building_name' => 'Field', 'latitude' => 10.3171, 'longitude' => 123.8881],
            ['room_code' => 'Field Area 3', 'building_name' => 'Field', 'latitude' => 10.3172, 'longitude' => 123.8882],
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
