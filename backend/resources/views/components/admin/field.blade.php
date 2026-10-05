@props(['label', 'name', 'required' => false, 'hint' => null])

<div {{ $attributes }}>
    <label for="{{ $name }}" class="admin-label">
        {{ $label }}
        @if ($required)
            <span class="text-[#dc3c45]" aria-hidden="true">*</span>
        @endif
    </label>
    {{ $slot }}
    @if ($hint)
        <p class="mt-1 text-xs text-slate">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-xs text-[#c0262f]">{{ $message }}</p>
    @enderror
</div>
