@extends('layouts.admin')
@section('title', $slide->exists ? 'Edit Slide' : 'New Slide')

@section('content')
    <form method="POST"
          action="{{ $slide->exists ? route('admin.slides.update', $slide) : route('admin.slides.store') }}"
          enctype="multipart/form-data"
          class="w-full space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        @csrf
        @if ($slide->exists) @method('PUT') @endif

        {{-- Fields, full width --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium">Name</label>
            <input type="text" name="name" value="{{ old('name', $slide->name) }}" required
                   class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700">
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Type</label>
                <div class="flex gap-4" data-type-toggle>
                    @foreach (['image' => 'Uploaded image', 'iframe' => 'Embedded page (iframe)'] as $val => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="type" value="{{ $val }}" data-type-radio
                                   {{ old('type', $slide->type) === $val ? 'checked' : '' }}
                                   class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Caption <span class="text-gray-400">(optional)</span></label>
                <input type="text" name="caption" value="{{ old('caption', $slide->caption) }}"
                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Duration (seconds)</label>
                <input type="number" min="1" max="120" step="0.5"
                       value="{{ old('duration_seconds', number_format(old('duration_ms', $slide->duration_ms ?: 8000) / 1000, 1)) }}"
                       oninput="this.form.duration_ms.value = Math.round(this.value*1000)"
                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                <input type="hidden" name="duration_ms" value="{{ old('duration_ms', $slide->duration_ms ?: 8000) }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Start date</label>
                <input type="date" name="starts_on" value="{{ old('starts_on', optional($slide->starts_on)->format('Y-m-d')) }}"
                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">End date</label>
                <input type="date" name="ends_on" value="{{ old('ends_on', optional($slide->ends_on)->format('Y-m-d')) }}"
                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700">
            </div>
        </div>
        <p class="-mt-3 text-xs text-gray-400">Leave the dates blank to show the slide indefinitely.</p>

        <div>
            <span class="mb-1.5 block text-sm font-medium">Locations</span>
            <div class="flex flex-wrap gap-4">
                @forelse ($locations as $loc)
                    <label class="flex items-center gap-1.5 text-sm">
                        <input type="checkbox" name="locations[]" value="{{ $loc->id }}"
                               {{ collect(old('locations', $slide->locations->pluck('id')->all()))->contains($loc->id) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        {{ $loc->name }}
                    </label>
                @empty
                    <span class="text-sm text-gray-400">No locations defined.</span>
                @endforelse
                <span class="text-xs text-gray-400">Leave all unchecked to show everywhere.</span>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slide->is_active) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            Active (show in the rotation)
        </label>

        {{-- Media / preview, full width below the fields --}}
        <div class="border-t border-gray-100 pt-6 dark:border-gray-700">
            <div data-type-panel="image">
                <label class="mb-2 block text-sm font-medium">Image {{ $slide->exists ? '(leave blank to keep current)' : '' }}</label>
                <div class="mb-4 flex max-h-[620px] w-full items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-100 p-2 dark:border-gray-700 dark:bg-gray-900">
                    @if ($slide->image_url)
                        <img src="{{ $slide->image_url }}" alt="" class="max-h-[600px] w-auto max-w-full object-contain">
                    @else
                        <span class="py-24 text-sm text-gray-400">No image yet — upload one below</span>
                    @endif
                </div>
                <input type="file" name="image" accept="image/*"
                       class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-500 file:me-4 file:cursor-pointer file:rounded-l-lg file:border-0 file:bg-gray-200 file:px-5 file:py-2.5 file:font-medium file:text-gray-800 hover:file:bg-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:file:bg-gray-600 dark:file:text-gray-100">
                <p class="mt-1 text-xs text-gray-400">Best at 1920×1080. JPG, PNG, GIF or WebP, up to 20 MB.</p>
            </div>

            <div data-type-panel="iframe">
                <label class="mb-2 block text-sm font-medium">Embed URL</label>
                <input type="url" name="iframe_url" value="{{ old('iframe_url', $slide->iframe_url) }}" placeholder="https://…"
                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                <p class="mt-1 text-xs text-gray-400">The page is embedded full-screen (1920×1080) on the TV.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 border-t border-gray-100 pt-5 dark:border-gray-700">
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                {{ $slide->exists ? 'Save Changes' : 'Create Slide' }}
            </button>
            <a href="{{ route('admin.slides.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
        </div>
    </form>

    <script>
        (function () {
            const radios = document.querySelectorAll('[data-type-radio]');
            const panels = document.querySelectorAll('[data-type-panel]');
            function sync() {
                const val = document.querySelector('[data-type-radio]:checked')?.value;
                panels.forEach(p => p.style.display = p.getAttribute('data-type-panel') === val ? '' : 'none');
            }
            radios.forEach(r => r.addEventListener('change', sync));
            sync();
        })();
    </script>
@endsection
