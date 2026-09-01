<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        return view('admin.locations.index', [
            'locations' => Location::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $this->applyDefault($data);
        Location::create($data);

        return back()->with('status', 'Location added.');
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $this->validateData($request);
        $this->applyDefault($data, $location->id);
        $location->update($data);

        return back()->with('status', 'Location updated.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return back()->with('status', 'Location removed.');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'ip_pattern' => ['nullable', 'string', 'max:64'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    /** Only one default zone at a time. */
    protected function applyDefault(array $data, ?int $exceptId = null): void
    {
        if (! empty($data['is_default'])) {
            Location::when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->update(['is_default' => false]);
        }
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'zone';
        $slug = $base;
        $i = 2;
        while (Location::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
