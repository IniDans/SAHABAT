<li class="flex items-start gap-2 rounded-lg border border-[#e5e5e5] bg-[#fafafa] p-3" data-repeater-baris>
    <div class="{{ $kelasGrid }} flex-1">
        @foreach ($kolom as $field)
            @php
                $id = "{$name}-{$indeks}-{$field['key']}";
                $kunciError = "{$name}.{$indeks}.{$field['key']}";
                $isi = $nilai[$field['key']] ?? '';
            @endphp
            <div class="{{ $field['lebar'] ?? '' }}">
                <label for="{{ $id }}" class="mb-1 block text-xs text-slate">{{ $field['label'] }}</label>
                @if (($field['tipe'] ?? 'text') === 'textarea')
                    <textarea id="{{ $id }}" name="{{ $name }}[{{ $indeks }}][{{ $field['key'] }}]" rows="{{ $field['baris'] ?? 3 }}" maxlength="{{ $field['maxlength'] ?? 255 }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="admin-input" @error($kunciError) aria-invalid="true" @enderror>{{ $isi }}</textarea>
                @else
                    <input id="{{ $id }}" name="{{ $name }}[{{ $indeks }}][{{ $field['key'] }}]" type="{{ $field['tipe'] ?? 'text' }}" value="{{ $isi }}" maxlength="{{ $field['maxlength'] ?? 255 }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="admin-input" @error($kunciError) aria-invalid="true" @enderror>
                @endif
                @error($kunciError)
                    <p class="mt-1 text-xs text-[#c0262f]">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>
    <button type="button" class="admin-icon-btn mt-5 hover:text-[#c0262f]" aria-label="Hapus baris" title="Hapus baris" data-repeater-hapus>
        <x-admin.icon name="trash" width="16" height="16" />
    </button>
</li>
