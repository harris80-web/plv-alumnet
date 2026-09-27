{{--
    Renders one column of a DocumentPlanner plan. Shared by both synthetic
    templates so the block vocabulary is defined in exactly one place.

    A two-cell block becomes a real two-column table row, which is what puts a
    genuine horizontal gap between a job title and its date — the thing the
    featurizer detects as a row fragment.

    Receives: $blocks, $variant.
--}}
@foreach ($blocks as $block)
    @php $cells = $block['cells']; @endphp

    @switch($block['role'])
        @case('title')
            <div class="doc-title" style="text-align: {{ $block['align'] ?? 'left' }}">{{ $cells[0]['text'] }}</div>
            @break

        @case('contact')
            <div class="contact" style="text-align: {{ $block['align'] ?? 'left' }}">{{ $cells[0]['text'] }}</div>
            @break

        @case('heading')
            <div class="heading">{{ $cells[0]['text'] }}</div>
            @break

        @case('paragraph')
            <p class="para">{{ $cells[0]['text'] }}</p>
            @break

        @case('sub')
            <div class="sub">{{ $cells[0]['text'] }}</div>
            @break

        @case('item')
            @if (count($cells) > 1)
                <table class="row item">
                    <tr>
                        <td class="row-left item-title">{{ $cells[0]['text'] }}</td>
                        <td class="row-right">{{ $cells[1]['text'] }}</td>
                    </tr>
                </table>
            @else
                <div class="item item-title">{{ $cells[0]['text'] }}</div>
            @endif
            @break

        @case('skills')
            @if (($block['style'] ?? 'flat') === 'labelled' && count($cells) > 1)
                <div class="item">
                    <span class="skill-label">{{ $cells[0]['text'] }}</span><span>{{ $cells[1]['text'] }}</span>
                </div>
            @elseif (($block['style'] ?? 'flat') === 'chips')
                <div class="item">
                    @foreach ($cells as $cell)<span class="chip">{{ $cell['text'] }}</span>@endforeach
                </div>
            @elseif (($block['style'] ?? 'flat') === 'stacked')
                @foreach ($cells as $cell)
                    <div>{{ $cell['text'] }}</div>
                @endforeach
            @else
                <p class="para">{{ $cells[0]['text'] }}</p>
            @endif
            @break

        @case('duty')
            @php
                $markup = $block['markup'] ?? 'ul';
                $glyph = $block['bullet'] ?? '•';
                $prefix = $glyph === 'none' ? '' : $glyph . ' ';
            @endphp
            @if ($markup === 'ul')
                <ul class="duties">
                    @foreach ($cells as $cell)
                        <li>{{ $cell['text'] }}</li>
                    @endforeach
                </ul>
            @elseif ($markup === 'p-joined')
                <p class="para">{{ $cells[0]['text'] }}</p>
            @else
                @foreach ($cells as $cell)
                    <p class="duty-line">{{ $prefix }}{{ $cell['text'] }}</p>
                @endforeach
            @endif
            @break
    @endswitch
@endforeach
