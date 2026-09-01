@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Active static slides', 'value' => $activeStaticCount.' / '.$staticCount, 'icon' => '🖼️'],
                ['label' => $monthName.' birthdays', 'value' => $birthdaysThisMonth, 'icon' => '🎂'],
                ['label' => $monthName.' anniversaries', 'value' => $anniversariesThisMonth, 'icon' => '🎉'],
                ['label' => 'Display locations', 'value' => $locations->count(), 'icon' => '📍'],
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

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 text-lg font-semibold">Quick actions</h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.slides.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ New static slide</a>
                <a href="{{ route('admin.monthly.index', ['tab' => 'birthdays']) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Import birthdays CSV</a>
                <a href="{{ route('admin.monthly.index', ['tab' => 'backgrounds']) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Update backgrounds</a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 text-lg font-semibold">Display links</h2>
            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">Point each factory TV at one of these URLs.</p>
            <ul class="space-y-2 text-sm">
                <li class="flex items-center justify-between gap-3">
                    <span class="font-medium">All / default</span>
                    <code class="rounded bg-gray-100 px-2 py-1 text-xs dark:bg-gray-700">{{ url('/') }}</code>
                </li>
                @foreach ($locations as $loc)
                    <li class="flex items-center justify-between gap-3">
                        <span class="font-medium">{{ $loc->name }}</span>
                        <code class="rounded bg-gray-100 px-2 py-1 text-xs dark:bg-gray-700">{{ url('/?location='.$loc->slug) }}</code>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Live slideshow preview --}}
    @php
        $defaultLoc = $locations->firstWhere('is_default', true) ?? $locations->first();
        $defaultSlug = $defaultLoc?->slug;
        $defaultSrc = url('/?preview=1'.($defaultSlug ? '&location='.$defaultSlug : ''));
    @endphp
    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Live preview</h2>
            <div class="flex flex-wrap gap-2" id="preview-switch">
                @foreach ($locations as $loc)
                    <button type="button" data-src="{{ url('/?preview=1&location='.$loc->slug) }}"
                            class="preview-btn rounded-lg border px-3 py-1.5 text-sm font-medium
                            {{ $loc->id === $defaultLoc?->id
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        {{ $loc->name }}
                    </button>
                @endforeach
                <button type="button" data-src="{{ url('/?preview=1') }}"
                        class="preview-btn rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                    All / default
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
