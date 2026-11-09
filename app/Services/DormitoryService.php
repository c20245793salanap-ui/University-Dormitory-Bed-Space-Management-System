<?php

namespace App\Services;

use App\Models\BedSlot;
use App\Models\DormBuilding;
use App\Models\DormContract;
use App\Models\DormRoom;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DormitoryService
{
    public function dashboardData(): array
    {
        $totalBeds = BedSlot::query()->count();
        $occupiedBeds = BedSlot::query()->where('status', 'Occupied')->count();

        return [
            'buildingCount' => DormBuilding::query()->count(),
            'roomCount' => DormRoom::query()->count(),
            'studentCount' => Student::query()->count(),
            'totalBeds' => $totalBeds,
            'occupiedBeds' => $occupiedBeds,
            'availableBeds' => $totalBeds - $occupiedBeds,
            'occupancyRate' => $totalBeds === 0 ? 0 : (int) round($occupiedBeds / $totalBeds * 100),
            'activeContracts' => DormContract::query()->where('lease_status', 'Active')->count(),
            'totalPaid' => DormContract::query()->sum('total_paid'),
            'recentContracts' => DormContract::query()
                ->with(['student', 'bedSlot.dormRoom.dormBuilding'])
                ->latest()
                ->take(5)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function managementData(string $resource, ?int $record = null, string $search = ''): array
    {
        $definition = $this->resourceDefinition($resource);
        $model = $definition['model'];
        $search = trim(strip_tags($search));
        $editing = $record === null ? null : $model::query()->findOrFail($record);
        $records = $model::query()
            ->with($definition['with'])
            ->when($search !== '', function ($query) use ($definition, $search): void {
                $query->where(function ($query) use ($definition, $search): void {
                    foreach ($definition['searchable'] as $column) {
                        $query->orWhere($column, 'like', '%'.$search.'%');
                    }
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return [
            'resource' => $resource,
            'definition' => $definition,
            'records' => $records,
            'editing' => $editing,
            'search' => $search,
            'formOptions' => $this->formOptions($resource),
        ];
    }

    public function create(string $resource, array $data): Model
    {
        $model = $this->resourceDefinition($resource)['model'];
        $data = $this->sanitize($data);

        return DB::transaction(function () use ($model, $resource, $data): Model {
            $bedSlot = null;

            if ($resource === 'contracts') {
                $bedSlot = BedSlot::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $data['bed_slot_id']);
                $this->ensureVacantBedSlot($bedSlot);
            }

            $entry = $model::query()->create($data);

            if ($resource === 'contracts' && $entry instanceof DormContract && $entry->lease_status === 'Active') {
                $bedSlot->update(['status' => 'Occupied']);
            }

            return $entry;
        });
    }

    public function update(string $resource, int $record, array $data): Model
    {
        $model = $this->resourceDefinition($resource)['model'];
        $data = $this->sanitize($data);

        return DB::transaction(function () use ($model, $resource, $record, $data): Model {
            $entry = $model::query()->findOrFail($record);
            $previousBedSlotId = $entry instanceof DormContract ? $entry->bed_slot_id : null;
            $wasActive = $entry instanceof DormContract && $entry->lease_status === 'Active';

            if ($resource === 'contracts' && $entry instanceof DormContract) {
                $targetBedSlotId = (int) $data['bed_slot_id'];
                $isSameActiveSlot = $wasActive && $targetBedSlotId === $previousBedSlotId;

                if (! $isSameActiveSlot) {
                    $bedSlot = BedSlot::query()
                        ->lockForUpdate()
                        ->findOrFail($targetBedSlotId);
                    $this->ensureVacantBedSlot($bedSlot, $entry->id);
                }
            }

            $entry->update($data);

            if ($resource === 'contracts' && $entry instanceof DormContract) {
                if ($wasActive && ($entry->lease_status !== 'Active' || $previousBedSlotId !== $entry->bed_slot_id)) {
                    $this->syncBedSlotStatus($previousBedSlotId);
                }

                if ($entry->lease_status === 'Active') {
                    $this->syncBedSlotStatus($entry->bed_slot_id);
                }
            }

            return $entry;
        });
    }

    public function delete(string $resource, int $record): bool
    {
        $model = $this->resourceDefinition($resource)['model'];

        return DB::transaction(function () use ($model, $resource, $record): bool {
            if ($this->hasDependentRecords($resource, $record)) {
                return false;
            }

            $entry = $model::query()->findOrFail($record);
            $activeBedSlotId = $entry instanceof DormContract && $entry->lease_status === 'Active'
                ? $entry->bed_slot_id
                : null;
            $entry->delete();

            if ($activeBedSlotId !== null) {
                $this->syncBedSlotStatus($activeBedSlotId);
            }

            return true;
        });
    }

    private function syncBedSlotStatus(int $bedSlotId): void
    {
        $hasActiveContract = DormContract::query()
            ->where('bed_slot_id', $bedSlotId)
            ->where('lease_status', 'Active')
            ->exists();

        BedSlot::query()
            ->whereKey($bedSlotId)
            ->update(['status' => $hasActiveContract ? 'Occupied' : 'Vacant']);
    }

    private function ensureVacantBedSlot(BedSlot $bedSlot, ?int $exceptContractId = null): void
    {
        $hasActiveContract = DormContract::query()
            ->where('bed_slot_id', $bedSlot->id)
            ->where('lease_status', 'Active')
            ->when($exceptContractId !== null, fn ($query) => $query->whereKeyNot($exceptContractId))
            ->exists();

        if ($bedSlot->status !== 'Vacant' || $hasActiveContract) {
            throw ValidationException::withMessages([
                'bed_slot_id' => 'This bed slot is no longer vacant. Choose an available bed slot.',
            ]);
        }
    }

    private function hasDependentRecords(string $resource, int $record): bool
    {
        return match ($resource) {
            'students' => DormContract::query()->where('student_id', $record)->exists(),
            'buildings' => DormRoom::query()->where('dorm_building_id', $record)->exists(),
            'rooms' => BedSlot::query()->where('dorm_room_id', $record)->exists(),
            'bed-slots' => DormContract::query()->where('bed_slot_id', $record)->exists(),
            default => false,
        };
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    private function formOptions(string $resource): array
    {
        return match ($resource) {
            'rooms' => [
                'dorm_building_id' => DormBuilding::query()
                    ->orderBy('building_name')
                    ->pluck('building_name', 'id')
                    ->all(),
            ],
            'bed-slots' => [
                'dorm_room_id' => DormRoom::query()
                    ->with('dormBuilding')
                    ->orderBy('room_number')
                    ->get()
                    ->mapWithKeys(fn (DormRoom $room): array => [
                        $room->id => $room->dormBuilding->building_name.' · Room '.$room->room_number,
                    ])
                    ->all(),
                'status' => ['Vacant' => 'Vacant', 'Occupied' => 'Occupied'],
            ],
            'contracts' => [
                'student_id' => Student::query()
                    ->orderBy('student_name')
                    ->get()
                    ->mapWithKeys(fn (Student $student): array => [
                        $student->id => $student->student_name.' ('.$student->student_id.')',
                    ])
                    ->all(),
                'bed_slot_id' => BedSlot::query()
                    ->with('dormRoom.dormBuilding')
                    ->orderBy('bed_slot')
                    ->get()
                    ->mapWithKeys(fn (BedSlot $slot): array => [
                        $slot->id => $slot->dormRoom->dormBuilding->building_name
                            .' · '.$slot->dormRoom->room_number
                            .' · Bed '.$slot->bed_slot,
                    ])
                    ->all(),
                'lease_status' => ['Pending' => 'Pending', 'Active' => 'Active'],
            ],
            default => [],
        };
    }

    /**
     * @return array{
     *     title: string,
     *     singular: string,
     *     model: class-string<Model>,
     *     fields: array<int, array{name: string, label: string, type: string, step?: string}>,
     *     columns: array<int, array{label: string, value: string}>,
     *     searchable: array<int, string>,
     *     with: array<int, string>,
     *     routePrefix: string,
     *     routeParameter: string
     * }
     */
    private function resourceDefinition(string $resource): array
    {
        return match ($resource) {
            'students' => [
                'title' => 'Students',
                'singular' => 'Student',
                'model' => Student::class,
                'fields' => [
                    ['name' => 'student_id', 'label' => 'Student ID', 'type' => 'text'],
                    ['name' => 'student_name', 'label' => 'Student name', 'type' => 'text'],
                ],
                'columns' => [
                    ['label' => 'Student ID', 'value' => 'student_id'],
                    ['label' => 'Student name', 'value' => 'student_name'],
                ],
                'searchable' => ['student_id', 'student_name'],
                'with' => [],
                'routePrefix' => 'dormitory.students',
                'routeParameter' => 'student',
            ],
            'buildings' => [
                'title' => 'Dorm buildings',
                'singular' => 'Building',
                'model' => DormBuilding::class,
                'fields' => [
                    ['name' => 'building_name', 'label' => 'Building name', 'type' => 'text'],
                ],
                'columns' => [
                    ['label' => 'Building name', 'value' => 'building_name'],
                ],
                'searchable' => ['building_name'],
                'with' => [],
                'routePrefix' => 'dormitory.buildings',
                'routeParameter' => 'building',
            ],
            'rooms' => [
                'title' => 'Dorm rooms',
                'singular' => 'Room',
                'model' => DormRoom::class,
                'fields' => [
                    ['name' => 'dorm_building_id', 'label' => 'Building', 'type' => 'select'],
                    ['name' => 'room_number', 'label' => 'Room number', 'type' => 'text'],
                ],
                'columns' => [
                    ['label' => 'Building', 'value' => 'dormBuilding.building_name'],
                    ['label' => 'Room number', 'value' => 'room_number'],
                ],
                'searchable' => ['room_number'],
                'with' => ['dormBuilding'],
                'routePrefix' => 'dormitory.rooms',
                'routeParameter' => 'room',
            ],
            'bed-slots' => [
                'title' => 'Bed slots',
                'singular' => 'Bed slot',
                'model' => BedSlot::class,
                'fields' => [
                    ['name' => 'dorm_room_id', 'label' => 'Room', 'type' => 'select'],
                    ['name' => 'bed_slot', 'label' => 'Bed slot', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select'],
                ],
                'columns' => [
                    ['label' => 'Building', 'value' => 'dormRoom.dormBuilding.building_name'],
                    ['label' => 'Room', 'value' => 'dormRoom.room_number'],
                    ['label' => 'Bed slot', 'value' => 'bed_slot'],
                    ['label' => 'Status', 'value' => 'status'],
                ],
                'searchable' => ['bed_slot', 'status'],
                'with' => ['dormRoom.dormBuilding'],
                'routePrefix' => 'dormitory.bed-slots',
                'routeParameter' => 'bedSlot',
            ],
            'contracts' => [
                'title' => 'Dorm contracts',
                'singular' => 'Contract',
                'model' => DormContract::class,
                'fields' => [
                    ['name' => 'student_id', 'label' => 'Student', 'type' => 'select'],
                    ['name' => 'bed_slot_id', 'label' => 'Bed slot', 'type' => 'select'],
                    ['name' => 'monthly_rate', 'label' => 'Monthly rate', 'type' => 'number', 'step' => '0.01'],
                    ['name' => 'contract_start', 'label' => 'Contract start', 'type' => 'date'],
                    ['name' => 'contract_end', 'label' => 'Contract end', 'type' => 'date'],
                    ['name' => 'security_deposit', 'label' => 'Security deposit', 'type' => 'number', 'step' => '0.01'],
                    ['name' => 'total_paid', 'label' => 'Total paid', 'type' => 'number', 'step' => '0.01'],
                    ['name' => 'lease_status', 'label' => 'Lease status', 'type' => 'select'],
                ],
                'columns' => [
                    ['label' => 'Student', 'value' => 'student.student_name'],
                    ['label' => 'Room', 'value' => 'bedSlot.dormRoom.room_number'],
                    ['label' => 'Bed', 'value' => 'bedSlot.bed_slot'],
                    ['label' => 'Monthly rate', 'value' => 'monthly_rate'],
                    ['label' => 'Security deposit', 'value' => 'security_deposit'],
                    ['label' => 'Lease status', 'value' => 'lease_status'],
                ],
                'searchable' => ['lease_status'],
                'with' => ['student', 'bedSlot.dormRoom.dormBuilding'],
                'routePrefix' => 'dormitory.contracts',
                'routeParameter' => 'contract',
            ],
            default => abort(404),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim(strip_tags($value));
            }
        }

        if (isset($data['student_id']) && is_string($data['student_id'])) {
            $data['student_id'] = Str::upper(preg_replace('/\s+/', '', $data['student_id']));
        }

        if (isset($data['building_name']) && is_string($data['building_name'])) {
            $data['building_name'] = Str::title($data['building_name']);
        }

        return $data;
    }
}
