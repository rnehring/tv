<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAnniversary;
use App\Models\EmployeeBirthday;
use App\Models\MonthlyBackground;
use App\Services\AdpCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MonthlyController extends Controller
{
    /** 1-based month names for the pickers. */
    public const MONTHS = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];

    public function index(Request $request): View
    {
        $month = (int) $request->query('month', now()->month);
        $month = ($month >= 1 && $month <= 12) ? $month : (int) now()->month;
        $tab = $request->query('tab', 'birthdays');

        return view('admin.monthly.index', [
            'month' => $month,
            'tab' => in_array($tab, ['birthdays', 'anniversaries', 'backgrounds'], true) ? $tab : 'birthdays',
            'months' => self::MONTHS,
            'birthdays' => EmployeeBirthday::where('month', $month)->orderBy('day')->orderBy('last_name')->get(),
            'anniversaries' => EmployeeAnniversary::where('month', $month)->orderBy('day')->orderBy('last_name')->get(),
            'birthdayBg' => MonthlyBackground::for($month, MonthlyBackground::KIND_BIRTHDAY),
            'anniversaryBg' => MonthlyBackground::for($month, MonthlyBackground::KIND_ANNIVERSARY),
        ]);
    }

    // ---- Birthdays -------------------------------------------------------

    public function importBirthdays(Request $request, AdpCsvImporter $importer): RedirectResponse
    {
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
        $result = $importer->importBirthdays($request->file('csv')->getRealPath());

        return $this->afterImport('birthdays', $request, $result);
    }

    public function storeBirthday(Request $request): RedirectResponse
    {
        $data = $this->validatePerson($request);
        EmployeeBirthday::create($data + ['imported_on' => now()->toDateString()]);

        return $this->redirectMonthly('birthdays', $data['month'])->with('status', 'Birthday added.');
    }

    public function updateBirthday(Request $request, EmployeeBirthday $birthday): RedirectResponse
    {
        $birthday->update($this->validatePerson($request));

        return $this->redirectMonthly('birthdays', $birthday->month)->with('status', 'Birthday updated.');
    }

    public function destroyBirthday(EmployeeBirthday $birthday): RedirectResponse
    {
        $month = $birthday->month;
        $birthday->delete();

        return $this->redirectMonthly('birthdays', $month)->with('status', 'Birthday removed.');
    }

    // ---- Anniversaries ---------------------------------------------------

    public function importAnniversaries(Request $request, AdpCsvImporter $importer): RedirectResponse
    {
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
        $result = $importer->importAnniversaries($request->file('csv')->getRealPath());

        return $this->afterImport('anniversaries', $request, $result);
    }

    public function storeAnniversary(Request $request): RedirectResponse
    {
        $data = $this->validatePerson($request, true);
        EmployeeAnniversary::create($data + ['imported_on' => now()->toDateString()]);

        return $this->redirectMonthly('anniversaries', $data['month'])->with('status', 'Anniversary added.');
    }

    public function updateAnniversary(Request $request, EmployeeAnniversary $anniversary): RedirectResponse
    {
        $anniversary->update($this->validatePerson($request, true));

        return $this->redirectMonthly('anniversaries', $anniversary->month)->with('status', 'Anniversary updated.');
    }

    public function destroyAnniversary(EmployeeAnniversary $anniversary): RedirectResponse
    {
        $month = $anniversary->month;
        $anniversary->delete();

        return $this->redirectMonthly('anniversaries', $month)->with('status', 'Anniversary removed.');
    }

    // ---- Backgrounds -----------------------------------------------------

    public function updateBackground(Request $request, int $month, string $kind): RedirectResponse
    {
        abort_unless(in_array($kind, [MonthlyBackground::KIND_BIRTHDAY, MonthlyBackground::KIND_ANNIVERSARY], true), 404);
        abort_unless($month >= 1 && $month <= 12, 404);

        $data = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:20480'],
            'heading' => ['nullable', 'string', 'max:255'],
            'text_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'align' => ['required', 'in:left,center,right'],
        ]);

        $bg = MonthlyBackground::firstOrNew(['month' => $month, 'kind' => $kind]);
        $bg->heading = $data['heading'] ?? null;
        $bg->text_color = $data['text_color'];
        $bg->accent_color = $data['accent_color'];
        $bg->align = $data['align'];

        if ($request->hasFile('image')) {
            if ($bg->image_path) {
                Storage::disk('public')->delete($bg->image_path);
            }
            $bg->image_path = $request->file('image')->store('backgrounds', 'public');
        }
        $bg->save();

        return $this->redirectMonthly('backgrounds', $month)->with('status', ucfirst($kind).' background for '.self::MONTHS[$month].' saved.');
    }

    // ---- Helpers ---------------------------------------------------------

    protected function validatePerson(Request $request, bool $anniversary = false): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'day' => ['required', 'integer', 'min:1', 'max:31'],
            'department' => ['nullable', 'string', 'max:255'],
        ];
        if ($anniversary) {
            $rules['hire_date'] = ['nullable', 'date'];
        }

        $data = $request->validate($rules);
        $data['first_name'] = trim($data['first_name']);
        $data['last_name'] = trim($data['last_name']);

        return $data;
    }

    protected function afterImport(string $tab, Request $request, array $result): RedirectResponse
    {
        $month = (int) $request->query('month', now()->month);
        $msg = "Imported {$result['imported']}, updated {$result['updated']}, skipped {$result['skipped']}.";
        $redirect = $this->redirectMonthly($tab, $month)->with('status', $msg);

        if (! empty($result['errors'])) {
            $redirect->with('import_errors', array_slice($result['errors'], 0, 20));
        }

        return $redirect;
    }

    protected function redirectMonthly(string $tab, int $month): RedirectResponse
    {
        return redirect()->route('admin.monthly.index', ['tab' => $tab, 'month' => $month]);
    }
}
