<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDormRoomRequest;
use App\Http\Requests\UpdateDormRoomRequest;
use App\Services\DormitoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DormRoomController extends Controller
{
    public function __construct(private DormitoryService $service) {}

    public function index(Request $request): View
    {
        return view('dormitory.manage', $this->service->managementData(
            'rooms',
            search: $request->query('search', ''),
        ));
    }

    public function create(): View
    {
        return view('dormitory.manage', $this->service->managementData('rooms'));
    }

    public function store(StoreDormRoomRequest $request): RedirectResponse
    {
        $this->service->create('rooms', $request->validated());

        return to_route('dormitory.rooms.index')->with('success', 'Room added successfully.');
    }

    public function edit(int $room): View
    {
        return view('dormitory.manage', $this->service->managementData('rooms', $room));
    }

    public function update(UpdateDormRoomRequest $request, int $room): RedirectResponse
    {
        $this->service->update('rooms', $room, $request->validated());

        return to_route('dormitory.rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(int $room): RedirectResponse
    {
        if (! $this->service->delete('rooms', $room)) {
            return back()->with('error', 'This room has bed slots and cannot be deleted yet.');
        }

        return to_route('dormitory.rooms.index')->with('success', 'Room deleted successfully.');
    }
}
