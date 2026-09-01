<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ config('app.name') }} — Slideshow{{ $location ? ' · '.$location->name : '' }}</title>
    @vite('resources/js/kiosk.js')
    <style>:root { --transition: {{ $transitionMs }}ms; }</style>
</head>
<body data-reload="{{ $isPreview ? 0 : $reloadSeconds }}" data-speed="{{ $isPreview ? 2 : 1 }}">
<div id="viewport">
    <div id="stage" style="--transition: {{ $transitionMs }}ms;">
        @if ($slides->isEmpty())
            <div id="empty">
                No active slides right now.<br>
                Add some in the admin, or check the display date ranges.
            </div>
        @else
            <div id="deck">
                @foreach ($slides as $slide)
                    @php $duration = $slide['duration_ms']; @endphp

                    @if ($slide['type'] === 'image')
                        <div class="kiosk-slide type-image" data-duration="{{ $duration }}"
                             style="background-image:url('{{ $slide['image_url'] }}')"></div>

                    @elseif ($slide['type'] === 'iframe')
                        <div class="kiosk-slide type-iframe" data-duration="{{ $duration }}">
                            <iframe data-src="{{ $slide['iframe_url'] }}" src="{{ $slide['iframe_url'] }}"
                                    referrerpolicy="no-referrer"></iframe>
                        </div>

                    @elseif ($slide['type'] === 'gas')
                        <div class="kiosk-slide" data-duration="{{ $duration }}">
                            @include('slideshow._gas', ['slide' => $slide])
                        </div>

                    @elseif ($slide['type'] === 'otif')
                        <div class="kiosk-slide" data-duration="{{ $duration }}">
                            @include('slideshow._otif', ['slide' => $slide])
                        </div>

                    @else
                        <div class="kiosk-slide" data-duration="{{ $duration }}">
                            @include('slideshow._generated', ['slide' => $slide])
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
</body>
</html>
