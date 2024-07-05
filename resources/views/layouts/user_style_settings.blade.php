@php
    $userFontSize = Auth::user()->settings->accessibility_settings['font_size'] ?? null;
    $DefaultFont = Auth::user()->settings->accessibility_settings['site_fonts_disabled'] ?? null;
    $letterSpacing = Auth::user()->settings->accessibility_settings['letter_spacing'] ?? null;
    $lineHeight = Auth::user()->settings->accessibility_settings['word_spacing'] ?? null;
    $wordSpacing = Auth::user()->settings->accessibility_settings['line_height'] ?? null;
@endphp

<style>
    
    *:not(.fas, .far, .fad, .fal) {
        @if ($DefaultFont)
            font-family: Roboto Condensed, serif !important;
        @endif
    }

    .main-content, .sidebar {
        @if ($lineHeight)
            line-height: {{ $lineHeight }}rem;
        @endif
    }

    html {
        @if ($wordSpacing)
            word-spacing: {{ $wordSpacing }}rem;
        @endif
        @if ($userFontSize)
            font-size: {{ $userFontSize }}rem;
        @endif
        @if ($letterSpacing)
            letter-spacing: {{ $letterSpacing }}rem;
        @endif
    }
</style>
