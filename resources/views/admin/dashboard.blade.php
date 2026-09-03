@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    {{-- Quick actions, full-width top row --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-3 text-lg font-semibold">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.slides.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ New Static Slide</a>
            <a href="{{ route('admin.monthly.index', ['tab' => 'birthdays']) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Import Birthdays CSV</a>
            <a href="{{ route('admin.monthly.index', ['tab' => 'anniversaries']) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Import Work Anniversaries</a>
            <a href="{{ route('admin.monthly.index', ['tab' => 'backgrounds']) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Update Backgrounds</a>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Active Static Slides', 'value' => $activeStaticCount.' / '.$staticCount, 'icon' => '🖼️'],
                ['label' => $monthName.' Birthdays', 'value' => $birthdaysThisMonth, 'icon' => '🎂'],
                ['label' => $monthName.' Anniversaries', 'value' => $anniversariesThisMonth, 'icon' => '🎉'],
                ['label' => 'Display Locations', 'value' => $locations->count(), 'icon' => '📍'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-2 text-2xl">{{ $card['icon'] }}</div>
                <div class="text-3xl font-bold">{{ $card['value'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Display links, full-width dark striped table --}}
    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-1 text-lg font-semibold">Display Links</h2>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Point each factory TV at one of these URLs.</p>
        <div class="overflow-x-auto rounded-xl border border-gray-700">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-800 text-xs uppercase tracking-wide text-gray-300">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Location</th>
                        <th class="px-4 py-3 font-semibold">Display URL</th>
                    </tr>
                </thead>
                <tbody class="text-gray-100">
                    <tr class="odd:bg-gray-900 even:bg-gray-800/50">
                        <td class="px-4 py-3 font-medium">All / Default</td>
                        <td class="px-4 py-3"><code class="rounded bg-gray-700/60 px-2 py-1 text-xs text-gray-100">{{ route('slideshow') }}</code></td>
                    </tr>
                    @foreach ($locations as $loc)
                        <tr class="odd:bg-gray-900 even:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium">{{ $loc->name }}</td>
                            <td class="px-4 py-3"><code class="rounded bg-gray-700/60 px-2 py-1 text-xs text-gray-100">{{ route('slideshow', ['location' => $loc->slug]) }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Live slideshow preview --}}
    @php
        $defaultLoc = $locations->firstWhere('is_default', true) ?? $locations->first();
        $defaultSlug = $defaultLoc?->slug;
        $defaultSrc = route('slideshow', array_filter(['preview' => 1, 'location' => $defaultSlug]));
    @endphp
    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Live Preview</h2>
            <div class="flex flex-wrap gap-2" id="preview-switch">
                @foreach ($locations as $loc)
                    <button type="button" data-src="{{ route('slideshow', ['preview' => 1, 'location' => $loc->slug]) }}"
                            class="preview-btn rounded-lg border px-3 py-1.5 text-sm font-medium
                            {{ $loc->id === $defaultLoc?->id
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        {{ $loc->name }}
                    </button>
                @endforeach
                <button type="button" data-src="{{ route('slideshow', ['preview' => 1]) }}"
                        class="preview-btn rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                    All / Default
                </button>
            </div>
        </div>
        <div class="mb-3 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300">
            <span>⏩</span>
            <span>This preview runs at <strong>2× speed</strong> so you can review every slide quickly. On the actual TVs each slide shows for its full configured duration.</span>
        </div>
        <div class="aspect-video w-full overflow-hidden rounded-xl border border-gray-200 bg-black dark:border-gray-700">
            <iframe id="preview-frame" src="{{ $defaultSrc }}" class="h-full w-full" title="Slideshow preview" loading="lazy"></iframe>
        </div>
        <p class="mt-2 text-xs text-gray-400">Preview cycles the slides for the selected location (it doesn't auto-reload). Open the full screen from any Display link above.</p>
    </div>

    <script>
        (function () {
            const frame = document.getElementById('preview-frame');
            const btns = document.querySelectorAll('#preview-switch .preview-btn');
            const active = ['border-blue-600', 'bg-blue-600', 'text-white'];
            const idle = ['border-gray-300'];
            btns.forEach((b) => b.addEventListener('click', () => {
                frame.src = b.getAttribute('data-src');
                btns.forEach((x) => { x.classList.remove(...active); x.classList.add(...idle); });
                b.classList.add(...active); b.classList.remove(...idle);
            }));
        })();
    </script>
@endsection
