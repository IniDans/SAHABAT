{{--
    Daftar baris yang bisa ditambah/dihapus (lihat initRepeater di app.js).
    kolom: [['key' => 'nama', 'label' => 'Nama', 'tipe' => 'text'|'textarea', 'placeholder' => '...', 'maxlength' => 100, 'lebar' => 'sm:col-span-2']]
--}}
@props(['name', 'label', 'kolom', 'baris' => [], 'tambah' => 'Tambah baris', 'hint' => null])

@php
    $baris = array_values(old($name, $baris) ?: []);
    $kelasGrid = count($kolom) > 1 ? 'grid gap-2 sm:grid-cols-2' : 'grid gap-2';
@endphp

<fieldset {{ $attributes->merge(['class' => 'space-y-2']) }} data-repeater data-repeater-indeks="{{ count($baris) }}">
    <legend class="admin-label">{{ $label }}</legend>
    @if ($hint)
        <p class="-mt-1 text-xs text-slate">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-[#c0262f]">{{ $message }}</p>
    @enderror

    <ol class="space-y-2" data-repeater-daftar>
        @foreach ($baris as $indeks => $nilai)
            @include('admin.partials.repeater-baris', ['name' => $name, 'kolom' => $kolom, 'indeks' => $indeks, 'nilai' => $nilai, 'kelasGrid' => $kelasGrid])
        @endforeach
    </ol>

    <template data-repeater-template>
        @include('admin.partials.repeater-baris', ['name' => $name, 'kolom' => $kolom, 'indeks' => '__INDEX__', 'nilai' => [], 'kelasGrid' => $kelasGrid])
    </template>

    <button type="button" class="admin-btn admin-btn-secondary" data-repeater-tambah>
        <x-admin.icon name="plus" width="16" height="16" />
        {{ $tambah }}
    </button>
</fieldset>
