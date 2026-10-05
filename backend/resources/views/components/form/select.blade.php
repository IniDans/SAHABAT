@props(['options' => [], 'selected' => null, 'placeholder' => null])

<div class="relative">
    <select {{ $attributes->class('form-control') }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" @selected((string) $value === (string) $selected)>{{ $label }}</option>
        @endforeach
    </select>
    <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="" width="20" height="20" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 rotate-180">
</div>
