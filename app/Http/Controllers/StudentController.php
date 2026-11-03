<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Services\DormitoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(private DormitoryService $service) {}

    public function index(Request $request): View
    {
        return view('dormitory.manage', $this->service->managementData(
            'students',
            search: $request->query('search', ''),
        ));
    }

    public function create(): View
    {
        return view('dormitory.manage', $this->service->managementData('students'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $this->service->create('students', $request->validated());

        return to_route('dormitory.students.index')->with('success', 'Student added successfully.');
    }

    public function edit(int $student): View
    {
        return view('dormitory.manage', $this->service->managementData('students', $student));
    }

    public function update(UpdateStudentRequest $request, int $student): RedirectResponse
    {
        $this->service->update('students', $student, $request->validated());

        return to_route('dormitory.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(int $student): RedirectResponse
    {
        if (! $this->service->delete('students', $student)) {
            return back()->with('error', 'This student has contracts and cannot be deleted yet.');
        }

        return to_route('dormitory.students.index')->with('success', 'Student deleted successfully.');
    }
}
