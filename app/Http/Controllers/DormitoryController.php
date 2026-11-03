<?php

namespace App\Http\Controllers;

use App\Services\DormitoryService;
use Illuminate\Contracts\View\View;

class DormitoryController extends Controller
{
    public function __construct(private DormitoryService $service) {}

    public function dashboard(): View
    {
        return view('dormitory.dashboard', $this->service->dashboardData());
    }
}
