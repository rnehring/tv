<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAnniversary;
use App\Models\EmployeeBirthday;
use App\Models\Location;
use App\Models\Slide;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $month = (int) now()->month;

        return view('admin.dashboard', [
            'staticCount' => Slide::whereIn('type', [Slide::TYPE_IMAGE, Slide::TYPE_IFRAME])->count(),
            'activeStaticCount' => Slide::whereIn('type', [Slide::TYPE_IMAGE, Slide::TYPE_IFRAME])->where('is_active', true)->count(),
            'birthdaysThisMonth' => EmployeeBirthday::where('month', $month)->count(),
            'anniversariesThisMonth' => EmployeeAnniversary::where('month', $month)->count(),
            'locations' => Location::orderBy('sort_order')->get(),
            'monthName' => now()->format('F'),
        ]);
    }
}
