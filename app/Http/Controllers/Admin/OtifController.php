<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtifSnapshot;
use App\Services\OtifFetcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OtifController extends Controller
{
    public function index(): View
    {
        return view('admin.otif.index', [
            'snapshot' => OtifSnapshot::latestFresh(),
            'source' => config('otif.source'),
            'companies' => config('otif.companies', []),
            'refreshMinutes' => config('otif.refresh_minutes'),
        ]);
    }

    public function refresh(OtifFetcher $fetcher): RedirectResponse
    {
        if (config('otif.source') !== 'epicor') {
            return back()->with('status', 'OTIF is in sample mode — set OTIF_SOURCE=epicor in .env to pull live data.');
        }

        $snapshot = $fetcher->fetch();

        if ($snapshot->error) {
            return back()->with('status', 'Fetch failed: '.$snapshot->error);
        }

        return back()->with('status', 'Backlog refreshed from Epicor ('.$snapshot->companies->count().' business units).');
    }
}
