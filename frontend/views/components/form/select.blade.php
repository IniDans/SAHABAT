{{-- options: [nilai => label], atau [judul grup => [nilai => label]] untuk <optgroup>. --}}
@props(['options' => [], 'selected' => null, 'placeholder' => null])

<div class="relative">
    <select {{ $attributes->class('form-control') }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $label)
            @if (is_iterable($label))
                <optgroup label="{{ $value }}">
                    @foreach ($label as $nilaiGrup => $labelGrup)
                        <option value="{{ $nilaiGrup }}" @selected((string) $nilaiGrup === (string) $selected)>{{ $labelGrup }}</option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $value }}" @selected((string) $value === (string) $selected)>{{ $label }}</option>
            @endif
        @endforeach
    </select>
    <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="" width="20" height="20" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 rotate-180">
</div>
