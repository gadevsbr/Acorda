<?php

namespace App\Http\Controllers;

use App\Models\Employment;
use App\Models\Organization;
use App\Models\PayrollRecord;
use App\Models\Person;
use App\Models\Position;
use App\Models\Source;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $latestPayroll = PayrollRecord::query()
            ->where('is_latest', true)
            ->orderByDesc('reference_year')
            ->orderByDesc('reference_month')
            ->first(['reference_year', 'reference_month']);
        $payrollQuery = PayrollRecord::query()->where('is_latest', true);
        if ($latestPayroll !== null) {
            $payrollQuery->where('reference_year', $latestPayroll->reference_year)
                ->where('reference_month', $latestPayroll->reference_month);
        } else {
            $payrollQuery->whereRaw('1 = 0');
        }

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'stats' => [
                'organizations' => Organization::query()->where('is_current', true)->count(),
                'people' => Person::query()->count(),
                'activeEmployments' => Employment::query()->where('is_current', true)->count(),
                'positions' => Position::query()->count(),
                'payrollRecords' => (clone $payrollQuery)->count(),
                'netPayrollCents' => (int) (clone $payrollQuery)->sum('net_cents'),
                'payrollReference' => $latestPayroll === null ? null : sprintf('%02d/%04d', $latestPayroll->reference_month, $latestPayroll->reference_year),
                'operationalSources' => Source::query()->where('status', 'operational')->count(),
                'partialSources' => Source::query()->where('status', 'partial')->count(),
            ],
        ]);
    }
}
