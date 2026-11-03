<?php

namespace Database\Seeders;

use App\Models\BedSlot;
use App\Models\DormBuilding;
use App\Models\DormContract;
use App\Models\DormRoom;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DormitorySeeder extends Seeder
{
    public function run(): void
    {
        $students = collect([
            ['BSIS-2026-001', 'Juan Dela Cruz'],
            ['BSIS-2026-002', 'Maria Santos'],
            ['BSIS-2026-003', 'Pedro Reyes'],
            ['BSIS-2026-004', 'Ana Garcia'],
            ['BSIS-2026-005', 'Luis Mendoza'],
        ])->mapWithKeys(fn (array $student): array => [
            $student[0] => Student::firstOrCreate(
                ['student_id' => $student[0]],
                ['student_name' => $student[1]],
            ),
        ]);

        $buildingNames = ['Main Building', 'North Hall', 'South Hall', 'East Wing', 'West Wing'];
        $buildings = collect($buildingNames)->mapWithKeys(fn (string $name): array => [
            $name => DormBuilding::firstOrCreate(['building_name' => $name]),
        ]);

        $rooms = collect([
            ['Main Building', '101'],
            ['Main Building', '102'],
            ['North Hall', '201'],
            ['North Hall', '202'],
            ['South Hall', '301'],
        ])->mapWithKeys(function (array $room) use ($buildings): array {
            $record = DormRoom::firstOrCreate([
                'dorm_building_id' => $buildings[$room[0]]->id,
                'room_number' => $room[1],
            ]);

            return [$room[1] => $record];
        });

        $bedSlots = collect([
            ['101', 'A', 'Vacant'],
            ['101', 'B', 'Occupied'],
            ['102', 'A', 'Vacant'],
            ['201', 'A', 'Vacant'],
            ['202', 'B', 'Occupied'],
        ])->map(function (array $slot) use ($rooms): BedSlot {
            return BedSlot::firstOrCreate(
                [
                    'dorm_room_id' => $rooms[$slot[0]]->id,
                    'bed_slot' => $slot[1],
                ],
                ['status' => $slot[2]],
            );
        });

        $contracts = [
            ['BSIS-2026-001', 0, '2500.00', '2026-11-01', '2027-05-31', '2500.00', '0.00', 'Pending'],
            ['BSIS-2026-002', 2, '2200.00', '2026-10-15', '2027-04-14', '2200.00', '1100.00', 'Active'],
            ['BSIS-2026-003', 3, '2000.00', '2026-12-01', '2027-05-30', '2000.00', '0.00', 'Pending'],
            ['BSIS-2026-005', 1, '2300.00', '2026-09-01', '2027-02-28', '2300.00', '2300.00', 'Active'],
            ['BSIS-2026-004', 4, '2400.00', '2026-11-15', '2027-05-14', '2400.00', '0.00', 'Pending'],
        ];

        foreach ($contracts as [$studentId, $slotIndex, $rate, $start, $end, $deposit, $paid, $status]) {
            DormContract::firstOrCreate(
                [
                    'student_id' => $students[$studentId]->id,
                    'bed_slot_id' => $bedSlots[$slotIndex]->id,
                    'contract_start' => $start,
                ],
                [
                    'monthly_rate' => $rate,
                    'contract_end' => $end,
                    'security_deposit' => $deposit,
                    'total_paid' => $paid,
                    'lease_status' => $status,
                ],
            );
        }
    }
}
