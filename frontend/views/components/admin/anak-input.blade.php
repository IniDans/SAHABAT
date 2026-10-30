{{-- Isian satu kolom data anak. $konfigurasi: label, jenis (text/date/select/datalist), wajib, pilihan, atribut, kosong. --}}
@props(['id', 'name', 'konfigurasi', 'nilai' => '', 'galat' => false])

@if ($konfigurasi['jenis'] === 'select')
    <select id="{{ $id }}" name="{{ $name }}" class="admin-input pr-8" @required($konfigurasi['wajib']) @if ($galat) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
        <option value="">{{ $konfigurasi['kosong'] ?? 'Pilih '.strtolower($konfigurasi['label']) }}</option>
        @foreach ($konfigurasi['pilihan'] as $nilaiOpsi => $teks)
            <option value="{{ $nilaiOpsi }}" @selected($nilai === (string) $nilaiOpsi)>{{ $teks }}</option>
        @endforeach
    </select>
@else
    <input id="{{ $id }}" type="{{ $konfigurasi['jenis'] === 'date' ? 'date' : 'text' }}" name="{{ $name }}" value="{{ $nilai }}" class="admin-input" @required($konfigurasi['wajib']) @if ($konfigurasi['jenis'] === 'datalist') list="{{ $id }}-pilihan" @endif @if ($galat) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif {!! $konfigurasi['atribut'] ?? '' !!}>
    @if ($konfigurasi['jenis'] === 'datalist')
        <datalist id="{{ $id }}-pilihan">
            @foreach ($konfigurasi['pilihan'] as $teks)
                <option value="{{ $teks }}"></option>
            @endforeach
        </datalist>
    @endif
@endif
