<?php

namespace Tests\Feature;

use App\Models\BedSlot;
use App\Models\DormBuilding;
use App\Models\DormContract;
use App\Models\DormRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DormitoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_dashboard_shows_occupancy_and_recent_contracts(): void
    {
        $building = DormBuilding::factory()->create(['building_name' => 'North Hall']);
        $room = DormRoom::factory()->for($building, 'dormBuilding')->create(['room_number' => '201']);
        $slot = BedSlot::factory()->for($room, 'dormRoom')->create([
            'bed_slot' => 'A',
            'status' => 'Occupied',
        ]);
        $student = Student::factory()->create([
            'student_id' => 'BSIS-2026-001',
            'student_name' => 'Juan Dela Cruz',
        ]);
        DormContract::factory()->for($student)->for($slot, 'bedSlot')->create([
            'lease_status' => 'Active',
        ]);

        $this->get(route('dormitory.dashboard'))
            ->assertOk()
            ->assertSee('North Hall')
            ->assertSee('Juan Dela Cruz')
            ->assertSee('100%');
    }

    public function test_student_management_page_lists_records_and_renders_the_form(): void
    {
        Student::factory()->create([
            'student_id' => 'BSIS-2026-010',
            'student_name' => 'Ana Garcia',
        ]);

        $this->get(route('dormitory.students.index'))
            ->assertOk()
            ->assertSee('Add Student')
            ->assertSee('BSIS-2026-010')
            ->assertSee('Ana Garcia');
    }

    public function test_student_records_can_be_created_and_duplicate_student_ids_are_rejected(): void
    {
        $studentData = [
            'student_id' => ' bsis-2026-002 ',
            'student_name' => '<strong>Maria Santos</strong>',
        ];

        $this->post(route('dormitory.students.store'), $studentData)
            ->assertRedirect(route('dormitory.students.index'));

        $this->assertDatabaseHas('students', [
            'student_id' => 'BSIS-2026-002',
            'student_name' => 'Maria Santos',
        ]);

        $this->from(route('dormitory.students.index'))
            ->post(route('dormitory.students.store'), [
                'student_id' => '<strong>BSIS-2026-002</strong>',
                'student_name' => 'Another Student',
            ])
            ->assertRedirect(route('dormitory.students.index'))
            ->assertSessionHasErrors([
                'student_id' => 'That student ID is already registered.',
            ]);

        $this->assertDatabaseCount('students', 1);
    }

    public function test_room_numbers_are_unique_per_building_not_globally(): void
    {
        $firstBuilding = DormBuilding::factory()->create();
        $secondBuilding = DormBuilding::factory()->create();
        DormRoom::factory()->for($firstBuilding, 'dormBuilding')->create(['room_number' => '101']);

        $this->post(route('dormitory.rooms.store'), [
            'dorm_building_id' => $secondBuilding->id,
            'room_number' => '101',
        ])->assertRedirect(route('dormitory.rooms.index'));

        $this->from(route('dormitory.rooms.index'))
            ->post(route('dormitory.rooms.store'), [
                'dorm_building_id' => $firstBuilding->id,
                'room_number' => '101',
            ])
            ->assertRedirect(route('dormitory.rooms.index'))
            ->assertSessionHasErrors('room_number');

        $this->assertDatabaseCount('dorm_rooms', 2);
    }

    public function test_room_records_can_be_updated(): void
    {
        $building = DormBuilding::factory()->create();
        $room = DormRoom::factory()->for($building, 'dormBuilding')->create([
            'room_number' => '101',
        ]);

        $this->put(route('dormitory.rooms.update', ['room' => $room->id]), [
            'dorm_building_id' => $building->id,
            'room_number' => '102',
        ])->assertRedirect(route('dormitory.rooms.index'));

        $this->assertDatabaseHas('dorm_rooms', [
            'id' => $room->id,
            'room_number' => '102',
        ]);
    }

    public function test_building_names_are_title_cased_before_storage(): void
    {
        $this->post(route('dormitory.buildings.store'), [
            'building_name' => '  mAiN bUiLdInG  ',
        ])->assertRedirect(route('dormitory.buildings.index'));

        $this->assertDatabaseHas('dorm_buildings', [
            'building_name' => 'Main Building',
        ]);
    }

    public function test_contract_end_cannot_be_before_the_start_date(): void
    {
        $student = Student::factory()->create();
        $slot = BedSlot::factory()->create();

        $this->from(route('dormitory.contracts.index'))
            ->post(route('dormitory.contracts.store'), [
                'student_id' => $student->id,
                'bed_slot_id' => $slot->id,
                'monthly_rate' => 2500,
                'contract_start' => '2026-12-01',
                'contract_end' => '2026-11-30',
                'security_deposit' => 2500,
                'total_paid' => 0,
                'lease_status' => 'Pending',
            ])
            ->assertRedirect(route('dormitory.contracts.index'))
            ->assertSessionHasErrors('contract_end');

        $this->assertDatabaseCount('dorm_contracts', 0);
    }

    public function test_contracts_can_be_created_for_existing_students_and_bed_slots(): void
    {
        $student = Student::factory()->create();
        $slot = BedSlot::factory()->create();

        $contractData = [
            'student_id' => $student->id,
            'bed_slot_id' => $slot->id,
            'monthly_rate' => 2500,
            'contract_start' => '2026-11-01',
            'contract_end' => '2027-05-31',
            'security_deposit' => 2500,
            'total_paid' => 0,
            'lease_status' => 'Pending',
        ];

        $this->post(route('dormitory.contracts.store'), $contractData)
            ->assertRedirect(route('dormitory.contracts.index'));

        $this->assertDatabaseHas('dorm_contracts', [
            'student_id' => $student->id,
            'bed_slot_id' => $slot->id,
            'lease_status' => 'Pending',
        ]);
        $this->assertSame(
            '2026-11-01',
            DormContract::query()->firstOrFail()->contract_start->toDateString(),
        );
    }

    public function test_active_contract_lifecycle_synchronizes_bed_occupancy(): void
    {
        $student = Student::factory()->create();
        $secondStudent = Student::factory()->create();
        $slot = BedSlot::factory()->create(['status' => 'Vacant']);
        $contractData = [
            'student_id' => $student->id,
            'bed_slot_id' => $slot->id,
            'monthly_rate' => 2500,
            'contract_start' => '2026-11-01',
            'contract_end' => '2027-05-31',
            'security_deposit' => 2500,
            'total_paid' => 0,
            'lease_status' => 'Active',
        ];

        $this->post(route('dormitory.contracts.store'), $contractData)
            ->assertRedirect(route('dormitory.contracts.index'));
        $this->assertDatabaseHas('bed_slots', ['id' => $slot->id, 'status' => 'Occupied']);

        $this->from(route('dormitory.contracts.index'))
            ->post(route('dormitory.contracts.store'), [
                ...$contractData,
                'student_id' => $secondStudent->id,
            ])
            ->assertSessionHasErrors([
                'bed_slot_id' => 'This bed slot already has an active contract.',
            ]);

        $contract = DormContract::query()->firstOrFail();
        $this->put(route('dormitory.contracts.update', ['contract' => $contract->id]), [
            ...$contractData,
            'lease_status' => 'Pending',
        ])->assertRedirect(route('dormitory.contracts.index'));
        $this->assertDatabaseHas('bed_slots', ['id' => $slot->id, 'status' => 'Vacant']);

        $this->put(route('dormitory.contracts.update', ['contract' => $contract->id]), $contractData)
            ->assertRedirect(route('dormitory.contracts.index'));
        $this->assertDatabaseHas('bed_slots', ['id' => $slot->id, 'status' => 'Occupied']);

        $this->delete(route('dormitory.contracts.destroy', ['contract' => $contract->id]))
            ->assertRedirect(route('dormitory.contracts.index'));
        $this->assertDatabaseHas('bed_slots', ['id' => $slot->id, 'status' => 'Vacant']);
    }

    public function test_contract_creation_requires_an_available_bed_slot(): void
    {
        $student = Student::factory()->create();
        $slot = BedSlot::factory()->create(['status' => 'Occupied']);

        $this->from(route('dormitory.contracts.index'))
            ->post(route('dormitory.contracts.store'), [
                'student_id' => $student->id,
                'bed_slot_id' => $slot->id,
                'monthly_rate' => 2500,
                'contract_start' => '2026-11-01',
                'contract_end' => '2027-05-31',
                'security_deposit' => 2500,
                'total_paid' => 0,
                'lease_status' => 'Pending',
            ])
            ->assertRedirect(route('dormitory.contracts.index'))
            ->assertSessionHasErrors([
                'bed_slot_id' => 'This bed slot is no longer vacant. Choose an available bed slot.',
            ]);

        $this->assertDatabaseCount('dorm_contracts', 0);
    }

    public function test_contract_sign_off_is_rejected_if_the_bed_slot_is_no_longer_vacant(): void
    {
        $contract = DormContract::factory()->create([
            'lease_status' => 'Pending',
        ]);
        BedSlot::query()->whereKey($contract->bed_slot_id)->update(['status' => 'Occupied']);

        $this->from(route('dormitory.contracts.index'))
            ->put(route('dormitory.contracts.update', ['contract' => $contract->id]), [
                'student_id' => $contract->student_id,
                'bed_slot_id' => $contract->bed_slot_id,
                'monthly_rate' => $contract->monthly_rate,
                'contract_start' => $contract->contract_start->toDateString(),
                'contract_end' => $contract->contract_end->toDateString(),
                'security_deposit' => $contract->security_deposit,
                'total_paid' => $contract->total_paid,
                'lease_status' => 'Active',
            ])
            ->assertRedirect(route('dormitory.contracts.index'))
            ->assertSessionHasErrors([
                'bed_slot_id' => 'This bed slot is no longer vacant. Choose an available bed slot.',
            ]);

        $this->assertDatabaseHas('dorm_contracts', [
            'id' => $contract->id,
            'lease_status' => 'Pending',
        ]);
        $this->assertDatabaseHas('bed_slots', [
            'id' => $contract->bed_slot_id,
            'status' => 'Occupied',
        ]);
    }

    public function test_contract_edit_form_formats_dates_for_date_inputs(): void
    {
        $contract = DormContract::factory()->create([
            'contract_start' => '2026-11-01',
            'contract_end' => '2027-05-31',
        ]);

        $this->get(route('dormitory.contracts.edit', [
            'contract' => $contract->id,
        ]))
            ->assertOk()
            ->assertSee('value="2026-11-01"', false)
            ->assertSee('value="2027-05-31"', false);
    }

    public function test_building_with_rooms_cannot_be_deleted(): void
    {
        $building = DormBuilding::factory()->create();
        DormRoom::factory()->for($building, 'dormBuilding')->create();

        $this->delete(route('dormitory.buildings.destroy', [
            'building' => $building->id,
        ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('dorm_buildings', ['id' => $building->id]);
    }
}
