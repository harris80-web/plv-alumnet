{{--
    Renders a DocumentPlanner plan as a resume, for training-data generation only.
    Never shown to a user; resume-pdf.blade.php remains the live export and is
    deliberately left untouched so a mistake here cannot break a real download.

    Layout uses tables and text-align, never flexbox — dompdf silently ignores
    display:flex, which is exactly why the live template's .row rules do nothing.

    Receives: $plan (from DocumentPlanner), $variant (from LayoutVariantFactory).
--}}
@php
    $headingStyle = $variant['heading_style'] ?? 'caps-rule';
    $baseSize = $variant['font_size'] ?? 11;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Synthetic Resume</title>
    <style>
        @page { margin: {{ $variant['margin'] ?? 40 }}px; }

        body {
            font-family: '{{ $variant['font_family'] ?? 'Helvetica' }}';
            font-size: {{ $baseSize }}px;
            line-height: {{ $variant['line_height'] ?? 1.4 }};
            color: #222222;
        }

        .doc-title {
            font-size: {{ round($baseSize * 1.9) }}px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .contact { font-size: {{ round($baseSize * 0.92) }}px; color: #555555; }

        .heading {
            font-size: {{ round($baseSize * 1.08) }}px;
            font-weight: bold;
            margin: {{ round($baseSize * 1.5) }}px 0 {{ round($baseSize * 0.5) }}px;
            @if ($headingStyle === 'caps-rule')
                text-transform: uppercase; letter-spacing: 1px;
                border-bottom: 1px solid #cccccc; padding-bottom: 3px;
            @elseif ($headingStyle === 'caps-plain')
                text-transform: uppercase; letter-spacing: 1px;
            @elseif ($headingStyle === 'caps-bar')
                text-transform: uppercase; letter-spacing: 1px;
                border-left: 4px solid #333333; padding-left: 6px;
            @endif
        }

        .item { margin-top: {{ round($baseSize * 0.5) }}px; }
        .item-title { font-weight: bold; }
        .sub { font-style: italic; color: #666666; font-size: {{ round($baseSize * 0.92) }}px; }
        .duties { margin: 2px 0 0; padding-left: 14px; }
        .duty-line { margin: 1px 0 0; }
        .para { margin: 2px 0 0; }

        .chip {
            display: inline-block;
            background: #f0f0f0;
            border-radius: 9px;
            padding: 2px 9px;
            margin: 0 5px 4px 0;
            font-size: {{ round($baseSize * 0.92) }}px;
        }

        .skill-label {
            display: inline-block;
            font-weight: bold;
            width: 96px;
            vertical-align: top;
            font-size: {{ round($baseSize * 0.92) }}px;
        }

        table.cols { width: 100%; border-collapse: collapse; }
        td.sidebar { width: 31%; vertical-align: top; padding-right: 22px; }
        td.body-col { width: 69%; vertical-align: top; }
        table.row { width: 100%; border-collapse: collapse; }
        td.row-left { text-align: left; vertical-align: top; }
        td.row-right { text-align: right; vertical-align: top; white-space: nowrap; }
    </style>
</head>
<body>

@if ($plan['sidebar'])
    <table class="cols">
        <tr>
            <td class="sidebar">@include('alumni.partials.synthetic-blocks', ['blocks' => $plan['left'], 'variant' => $variant])</td>
            <td class="body-col">@include('alumni.partials.synthetic-blocks', ['blocks' => $plan['main'], 'variant' => $variant])</td>
        </tr>
    </table>
@else
    @include('alumni.partials.synthetic-blocks', ['blocks' => $plan['main'], 'variant' => $variant])
@endif

</body>
</html>
