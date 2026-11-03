<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBedSlotRequest;
use App\Http\Requests\UpdateBedSlotRequest;
use App\Services\DormitoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BedSlotController extends Controller
{
    public function __construct(private DormitoryService $service) {}

    public function index(Request $request): View
    {
        return view('dormitory.manage', $this->service->managementData(
            'bed-slots',
            search: $request->query('search', ''),
        ));
    }

    public function create(): View
    {
        return view('dormitory.manage', $this->service->managementData('bed-slots'));
    }

    public function store(StoreBedSlotRequest $request): RedirectResponse
    {
        $this->service->create('bed-slots', $request->validated());

        return to_route('dormitory.bed-slots.index')->with('success', 'Bed slot added successfully.');
    }

    public function edit(int $bedSlot): View
    {
        return view('dormitory.manage', $this->service->managementData('bed-slots', $bedSlot));
    }

    public function update(UpdateBedSlotRequest $request, int $bedSlot): RedirectResponse
    {
        $this->service->update('bed-slots', $bedSlot, $request->validated());

        return to_route('dormitory.bed-slots.index')->with('success', 'Bed slot updated successfully.');
    }

    public function destroy(int $bedSlot): RedirectResponse
    {
        if (! $this->service->delete('bed-slots', $bedSlot)) {
            return back()->with('error', 'This bed slot has contracts and cannot be deleted yet.');
        }

        return to_route('dormitory.bed-slots.index')->with('success', 'Bed slot deleted successfully.');
    }
}
