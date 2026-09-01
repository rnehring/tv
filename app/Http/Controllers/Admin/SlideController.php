<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Manages static slides (uploaded images and embedded iframes) plus the display
 * settings (active / duration / order / locations) of the generated
 * birthday & anniversary slides.
 */
class SlideController extends Controller
{
    public function index(): View
    {
        $slides = Slide::whereIn('type', [Slide::TYPE_IMAGE, Slide::TYPE_IFRAME])
            ->with('locations')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        $generated = Slide::whereIn('type', [
            Slide::TYPE_BIRTHDAY, Slide::TYPE_ANNIVERSARY, Slide::TYPE_GAS, Slide::TYPE_OTIF,
        ])->with('locations')->orderBy('sort_order')->get();

        return view('admin.slides.index', [
            'slides' => $slides,
            'generated' => $generated,
            'locations' => Location::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.slides.form', [
            'slide' => new Slide(['type' => Slide::TYPE_IMAGE, 'duration_ms' => 8000, 'is_active' => true]),
            'locations' => Location::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, true);

        $slide = new Slide();
        $this->fill($slide, $request, $data);
        $slide->sort_order = $data['sort_order'] ?? (Slide::max('sort_order') + 1);
        $slide->save();
        $slide->locations()->sync($data['locations'] ?? []);

        return redirect()->route('admin.slides.index')->with('status', 'Slide created.');
    }

    public function edit(Slide $slide): View
    {
        abort_if($slide->isGenerated(), 404);

        return view('admin.slides.form', [
            'slide' => $slide,
            'locations' => Location::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Slide $slide): RedirectResponse
    {
        // Generated slides only expose display settings, not media.
        if ($slide->isGenerated()) {
            $data = $request->validate([
                'duration_ms' => ['required', 'integer', 'min:1000', 'max:120000'],
                'is_active' => ['nullable', 'boolean'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'locations' => ['nullable', 'array'],
                'locations.*' => ['integer', 'exists:locations,id'],
            ]);
            $slide->update([
                'duration_ms' => $data['duration_ms'],
                'is_active' => $request->boolean('is_active'),
                'sort_order' => $data['sort_order'] ?? $slide->sort_order,
            ]);
            $slide->locations()->sync($data['locations'] ?? []);

            return back()->with('status', ucfirst($slide->type).' slide updated.');
        }

        $data = $this->validateData($request, false);
        $this->fill($slide, $request, $data);
        $slide->save();
        $slide->locations()->sync($data['locations'] ?? []);

        return redirect()->route('admin.slides.index')->with('status', 'Slide updated.');
    }

    public function toggle(Slide $slide): RedirectResponse
    {
        $slide->update(['is_active' => ! $slide->is_active]);

        return back()->with('status', $slide->name.' is now '.($slide->is_active ? 'on' : 'off').'.');
    }

    public function destroy(Slide $slide): RedirectResponse
    {
        abort_if($slide->isGenerated(), 403);

        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }
        $slide->delete();

        return back()->with('status', 'Slide deleted.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $order = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:slides,id'],
        ])['order'];

        foreach ($order as $position => $id) {
            Slide::where('id', $id)->update(['sort_order' => $position + 1]);
        }

        return back()->with('status', 'Order saved.');
    }

    protected function validateData(Request $request, bool $creating): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:image,iframe'],
            'caption' => ['nullable', 'string'],
            'image' => [$creating ? 'nullable' : 'nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:20480'],
            'iframe_url' => ['nullable', 'url', 'required_if:type,iframe'],
            'duration_ms' => ['required', 'integer', 'min:1000', 'max:120000'],
            'is_active' => ['nullable', 'boolean'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer', 'exists:locations,id'],
        ]);
    }

    protected function fill(Slide $slide, Request $request, array $data): void
    {
        $slide->name = $data['name'];
        $slide->type = $data['type'];
        $slide->caption = $data['caption'] ?? null;
        $slide->duration_ms = $data['duration_ms'];
        $slide->is_active = $request->boolean('is_active');
        $slide->starts_on = $data['starts_on'] ?? null;
        $slide->ends_on = $data['ends_on'] ?? null;

        if ($data['type'] === Slide::TYPE_IFRAME) {
            $slide->iframe_url = $data['iframe_url'];
        } else {
            $slide->iframe_url = null;
        }

        if ($request->hasFile('image')) {
            if ($slide->image_path) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $slide->image_path = $request->file('image')->store('slides', 'public');
        }
    }
}
