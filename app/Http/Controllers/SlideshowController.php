<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Setting;
use App\Services\SlideshowBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlideshowController extends Controller
{
    public function index(Request $request, SlideshowBuilder $builder): View
    {
        $location = Location::resolve($request->query('location'), $request->ip());

        $slides = $builder->build($location);

        $settings = Setting::map();

        return view('slideshow.index', [
            'slides' => $slides,
            'location' => $location,
            'reloadSeconds' => (int) ($settings['reload_seconds'] ?? 900),
            'transitionMs' => (int) ($settings['transition_ms'] ?? 1000),
            'isPreview' => $request->boolean('preview'),
        ]);
    }
}
