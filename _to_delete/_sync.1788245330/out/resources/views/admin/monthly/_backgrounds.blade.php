@php
    $cards = [
        ['kind' => 'birthday', 'label' => '🎂 Birthday background', 'bg' => $birthdayBg, 'textDefault' => '#3a2416'],
        ['kind' => 'anniversary', 'label' => '🎉 Anniversary background', 'bg' => $anniversaryBg, 'textDefault' => '#0d3b53'],
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

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Save</button>
                <a href="{{ route('slideshow', ['preview' => 1]) }}" target="_blank" class="text-sm text-blue-600 hover:underline">Preview slideshow →</a>
            </div>
        </form>
    @endforeach
</div>
