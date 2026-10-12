@props(['placeholder' => 'Cari...'])

{{-- Form filter daftar: kolom cari + select (slot) yang otomatis mengirim form. --}}
<form method="GET" class="flex flex-wrap items-center gap-2 border-b border-[#e5e5e5] p-4" role="search">
    <div class="relative min-w-[200px] flex-1">
        <x-admin.icon name="search" width="16" height="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate" />
        <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}" class="admin-input pl-9">
    </div>
    {{ $slot }}
    <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
    @if (request()->hasAny(['search', 'kategori', 'status', 'prioritas', 'program', 'dari', 'sampai']))
        <a href="{{ request()->url() }}" class="text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
    @endif
</form>
