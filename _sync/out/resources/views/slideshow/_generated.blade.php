@php
    $entries = $slide['entries'];
    $count = count($entries);
    $cols = $count <= 8 ? 1 : ($count <= 20 ? 2 : 3);
    $align = $slide['align'] ?? 'center';
    $bg = $slide['background_url'];
@endphp
<div class="gen align-{{ $align }}"
     style="color: {{ $slide['text_color'] }}; @if($bg) background-image:url('{{ $bg }}'); @endif">
    <div class="inner">
        @if (!empty($slide['heading']))
            <div class="title">{{ $slide['heading'] }}</div>
        @endif
        <div class="panel">
            <div class="grid" style="column-count: {{ $cols }};">
                @foreach ($entries as $entry)
                    <div class="entry {{ $entry['today'] ? 'today' : '' }}"
                         @if($entry['today']) style="color: {{ $slide['accent_color'] }};" @endif>
                        <span class="day">{{ $entry['day_label'] }}</span>
                        <span class="name">{{ $entry['name'] }}<span class="suffix">{{ $entry['suffix'] }}</span>@if($entry['today'])<span class="badge" style="background: {{ $slide['accent_color'] }};">TODAY</span>@endif</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
