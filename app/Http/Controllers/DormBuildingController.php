<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDormBuildingRequest;
use App\Http\Requests\UpdateDormBuildingRequest;
use App\Services\DormitoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DormBuildingController extends Controller
{
    public function __construct(private DormitoryService $service) {}

    public function index(Request $request): View
    {
        return view('dormitory.manage', $this->service->managementData(
            'buildings',
            search: $request->query('search', ''),
        ));
    }

    public function create(): View
    {
        return view('dormitory.manage', $this->service->managementData('buildings'));
    }

    public function store(StoreDormBuildingRequest $request): RedirectResponse
    {
        $this->service->create('buildings', $request->validated());

        return to_route('dormitory.buildings.index')->with('success', 'Building added successfully.');
    }

    public function edit(int $building): View
    {
        return view('dormitory.manage', $this->service->managementData('buildings', $building));
    }

    public function update(UpdateDormBuildingRequest $request, int $building): RedirectResponse
    {
        $this->service->update('buildings', $building, $request->validated());

        return to_route('dormitory.buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy(int $building): RedirectResponse
    {
        if (! $this->service->delete('buildings', $building)) {
            return back()->with('error', 'This building has rooms and cannot be deleted yet.');
        }

        return to_route('dormitory.buildings.index')->with('success', 'Building deleted successfully.');
    }
}
