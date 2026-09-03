<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Slide preview</title>
    @vite('resources/js/kiosk.js')
    <style>:root { --transition: 0ms; } html, body { background: #000; }</style>
</head>
<body data-reload="0" data-speed="1">
<div id="viewport">
    <div id="stage">
        <div class="kiosk-slide is-active">
            @include('slideshow._generated', ['slide' => $slide])
        </div>
    </div>
</div>
</body>
</html>
