<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDormContractRequest;
use App\Http\Requests\UpdateDormContractRequest;
use App\Services\DormitoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DormContractController extends Controller
{
    public function __construct(private DormitoryService $service) {}

    public function index(Request $request): View
    {
        return view('dormitory.manage', $this->service->managementData(
            'contracts',
            search: $request->query('search', ''),
        ));
    }

    public function create(): View
    {
        return view('dormitory.manage', $this->service->managementData('contracts'));
    }

    public function store(StoreDormContractRequest $request): RedirectResponse
    {
        $this->service->create('contracts', $request->validated());

        return to_route('dormitory.contracts.index')->with('success', 'Contract added successfully.');
    }

    public function edit(int $contract): View
    {
        return view('dormitory.manage', $this->service->managementData('contracts', $contract));
    }

    public function update(UpdateDormContractRequest $request, int $contract): RedirectResponse
    {
        $this->service->update('contracts', $contract, $request->validated());

        return to_route('dormitory.contracts.index')->with('success', 'Contract updated successfully.');
    }

    public function destroy(int $contract): RedirectResponse
    {
        if (! $this->service->delete('contracts', $contract)) {
            return back()->with('error', 'This contract cannot be deleted while it is linked to other records.');
        }

        return to_route('dormitory.contracts.index')->with('success', 'Contract deleted successfully.');
    }
}
