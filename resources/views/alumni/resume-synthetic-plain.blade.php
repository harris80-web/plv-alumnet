{{--
    A deliberately plain, word-processor-looking resume: serif or monospace body,
    no rules, no chips, no colour, wide margins, dates in a right-hand column.

    Structurally unlike both the app's own export and resume-synthetic.blade.php,
    so holding this whole template out of training measures generalisation to a
    layout family the model has never seen — which is the number that actually
    matters for "works even when the format is different every time".

    Receives: $plan, $variant.
--}}
@php $baseSize = $variant['font_size'] ?? 11; @endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Synthetic Resume</title>
    <style>
        @page { margin: {{ $variant['margin'] ?? 58 }}px; }

        body {
            font-family: '{{ $variant['font_family'] ?? 'Times' }}';
            font-size: {{ $baseSize }}px;
            line-height: {{ $variant['line_height'] ?? 1.4 }};
            color: #000000;
        }

        /* Noticeably flatter hierarchy than the designed template: the name is
           only slightly larger than the body, which makes doc_title harder to
           spot from size alone and is realistic for a plain export. */
        .doc-title { font-size: {{ round($baseSize * 1.35) }}px; font-weight: bold; }
        .contact { font-size: {{ $baseSize }}px; }
        .heading { font-weight: bold; text-transform: uppercase; margin: {{ round($baseSize * 1.6) }}px 0 {{ round($baseSize * 0.3) }}px; }
        .item { margin-top: {{ round($baseSize * 0.4) }}px; }
        .item-title { font-weight: bold; }
        .sub { font-size: {{ $baseSize }}px; }
        .duties { margin: 1px 0 0; padding-left: 18px; }
        .duty-line { margin: 0; }
        .para { margin: 1px 0 0; }
        .chip { display: inline; }
        .skill-label { display: inline-block; font-weight: bold; width: 110px; vertical-align: top; }

        table.cols { width: 100%; border-collapse: collapse; }
        td.sidebar { width: 30%; vertical-align: top; padding-right: 20px; }
        td.body-col { width: 70%; vertical-align: top; }
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
