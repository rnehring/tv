@php
    $cards = [
        ['kind' => 'birthday', 'label' => '🎂 Birthday Background', 'bg' => $birthdayBg, 'textDefault' => '#3a2416'],
        ['kind' => 'anniversary', 'label' => '🎉 Anniversary Background', 'bg' => $anniversaryBg, 'textDefault' => '#0d3b53'],
    ];
@endphp

<div class="grid gap-6 lg:grid-cols-2">
    @foreach ($cards as $card)
        @php $bg = $card['bg']; $kind = $card['kind']; @endphp
        <form method="POST" action="{{ route('admin.monthly.backgrounds.update', ['month' => $month, 'kind' => $kind]) }}"
              enctype="multipart/form-data"
              class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            @csrf @method('PUT')
            <h3 class="mb-3 text-base font-semibold">{{ $card['label'] }} — {{ $monthName }}</h3>

            <div class="mb-4 aspect-video overflow-hidden rounded-lg border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-700">
                @if ($bg && $bg->image_url)
                    <img src="{{ $bg->image_url }}" alt="" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-sm text-gray-400">No background uploaded</div>
                @endif
            </div>

            <label class="mb-1.5 block text-sm font-medium">Replace image</label>
            <input type="file" name="image" accept="image/*"
                   class="mb-4 block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-500 file:me-4 file:cursor-pointer file:rounded-l-lg file:border-0 file:bg-gray-200 file:px-5 file:py-2.5 file:font-medium file:text-gray-800 hover:file:bg-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:file:bg-gray-600 dark:file:text-gray-100">

            <label class="mb-1.5 block text-sm font-medium">Heading <span class="text-gray-400">(optional — leave blank if the artwork already has a title)</span></label>
            <input type="text" name="heading" value="{{ old('heading', $bg->heading ?? '') }}" placeholder="e.g. {{ $monthName }} Birthdays"
                   class="mb-4 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Name colour</label>
                    <div class="flex items-center gap-2">
                        <input type="color" data-color-sync="#text-{{ $kind }}" value="{{ $bg->text_color ?? $card['textDefault'] }}"
                               class="h-9 w-10 rounded border border-gray-300 dark:border-gray-600">
                        <input type="text" id="text-{{ $kind }}" name="text_color" value="{{ $bg->text_color ?? $card['textDefault'] }}"
                               class="w-28 rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">“Today” colour</label>
                    <div class="flex items-center gap-2">
                        <input type="color" data-color-sync="#accent-{{ $kind }}" value="{{ $bg->accent_color ?? '#c0392b' }}"
                               class="h-9 w-10 rounded border border-gray-300 dark:border-gray-600">
                        <input type="text" id="accent-{{ $kind }}" name="accent_color" value="{{ $bg->accent_color ?? '#c0392b' }}"
                               class="w-28 rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                    </div>
                </div>
            </div>

            <label class="mb-1.5 mt-4 block text-sm font-medium">Alignment</label>
            <select name="align" class="mb-4 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                @foreach (['center' => 'Centre', 'left' => 'Left', 'right' => 'Right'] as $val => $lbl)
                    <option value="{{ $val }}" {{ ($bg->align ?? 'center') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>

            {{-- Name size: live preview + slider --}}
            <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-700">
                <label class="mb-2 block text-sm font-medium">Name Size</label>
                <div class="aspect-video w-full overflow-hidden rounded-lg border border-gray-200 bg-black dark:border-gray-700">
                    <iframe data-fs-preview="{{ $kind }}"
                            src="{{ route('admin.monthly.preview', ['month' => $month, 'kind' => $kind]) }}"
                            class="h-full w-full" title="Name size preview" loading="lazy"></iframe>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-xs text-gray-400">A</span>
                    <input type="range" name="font_size" min="12" max="80" step="1"
                           value="{{ $bg->font_size ?? 34 }}" data-fs-slider="{{ $kind }}"
                           class="flex-1 accent-blue-600">
                    <span class="text-lg text-gray-400">A</span>
                    <span data-fs-value="{{ $kind }}" class="w-14 text-right text-sm tabular-nums text-gray-500 dark:text-gray-400"></span>
                </div>
                <label class="mt-2 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="fs_auto" value="1" data-fs-auto="{{ $kind }}"
                           {{ ($bg->font_size ?? null) ? '' : 'checked' }}
                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Auto-fit to the panel <span class="text-gray-400">(size the names automatically; ignore the slider)</span>
                </label>
            </div>

            <div class="mt-5 flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Save</button>
                <a href="{{ route('slideshow', ['preview' => 1]) }}" target="_blank" class="text-sm text-blue-600 hover:underline">Preview Slideshow →</a>
            </div>
        </form>
    @endforeach
</div>

<script>
    (function () {
        document.querySelectorAll('[data-fs-slider]').forEach(function (slider) {
            var kind = slider.getAttribute('data-fs-slider');
            var iframe = document.querySelector('[data-fs-preview="' + kind + '"]');
            var auto = document.querySelector('[data-fs-auto="' + kind + '"]');
            var valLabel = document.querySelector('[data-fs-value="' + kind + '"]');

            function post(value) {
                try { iframe.contentWindow.postMessage({ type: 'kiosk-fs', value: value }, '*'); } catch (e) {}
            }
            function sync(live) {
                if (auto.checked) {
                    slider.disabled = true;
                    valLabel.textContent = 'Auto';
                    if (live) post('auto');
                } else {
                    slider.disabled = false;
                    valLabel.textContent = slider.value + 'px';
                    if (live) post(parseInt(slider.value, 10));
                }
            }

            slider.addEventListener('input', function () {
                if (auto.checked) { auto.checked = false; }
                sync(true);
            });
            auto.addEventListener('change', function () { sync(true); });
            // Push the current state into the preview once it has loaded.
            iframe.addEventListener('load', function () { sync(true); });
            // Initial label state (before the iframe loads).
            sync(false);
        });
    })();
</script>
