@props(['name', 'label', 'autocomplete' => 'current-password', 'placeholder' => 'Masukkan password', 'hint' => null])

<div>
    <label for="{{ $name }}" class="block text-sm text-[#1e2328]">{{ $label }}</label>
    <div class="relative mt-1.5">
        <input id="{{ $name }}" name="{{ $name }}" type="password" required maxlength="255" autocomplete="{{ $autocomplete }}" placeholder="{{ $placeholder }}" @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror {{ $attributes->merge(['class' => 'auth-input pr-12']) }} data-password-input>
        <button type="button" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate hover:text-[#1e2328]" aria-label="Tampilkan password" aria-pressed="false" data-password-toggle>
            <x-admin.icon name="eye" width="24" height="24" data-icon-show />
            <x-admin.icon name="eye-off" width="24" height="24" class="hidden" data-icon-hide />
        </button>
    </div>
    @if ($hint)
        <p class="mt-1.5 text-xs text-slate">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
    @enderror
</div>
