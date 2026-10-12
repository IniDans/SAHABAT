@php
    use App\Enums\StatusPesan;

    $tone = match ($pesan->status) {
        StatusPesan::BelumDibaca => 'red',
        StatusPesan::Dibalas => 'green',
        default => 'gray',
    };
    $diarsipkan = $pesan->status === StatusPesan::Diarsipkan;
@endphp

<x-layouts.admin title="Detail Pesan">
    <div class="mb-6">
        <nav class="flex items-center gap-1 text-[13px] text-slate" aria-label="Breadcrumb">
            <a href="{{ route('admin.pesan.index') }}" class="inline-flex items-center gap-1 hover:text-[#1e2328]">
                <x-admin.icon name="arrow-left" width="12" height="12" />
                Pesan masuk
            </a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Detail pesan</span>
        </nav>
        <h1 class="mt-1 text-2xl font-semibold">{{ $pesan->subjek ?: '(Tanpa subjek)' }}</h1>
    </div>

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_300px]">
        <article class="admin-card p-6">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-[#f0f0f0] pb-4">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#e3f1ea] text-sm font-semibold text-footer" aria-hidden="true">{{ str($pesan->nama)->substr(0, 1)->upper() }}</span>
                    <div>
                        <p class="text-sm font-semibold">{{ $pesan->nama }}</p>
                        <a href="mailto:{{ $pesan->email }}" class="text-[13px] text-slate hover:text-footer">{{ $pesan->email }}</a>
                    </div>
                </div>
                <div class="text-right">
                    <x-admin.badge :tone="$tone" class="gap-1.5">
                        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                        {{ $pesan->status->value }}
                    </x-admin.badge>
                    <p class="mt-1 text-xs text-slate">
                        <time datetime="{{ $pesan->created_at->toIso8601String() }}">{{ $pesan->created_at->locale('id')->translatedFormat('l, j F Y \p\u\k\u\l H.i') }}</time>
                    </p>
                </div>
            </header>

            <div class="pt-5 text-sm leading-relaxed break-words whitespace-pre-line">{{ $pesan->isi }}</div>
        </article>

        <aside class="admin-card space-y-2 p-5" aria-label="Tindakan">
            <h2 class="mb-3 text-[15px] font-semibold">Tindakan</h2>

            <form action="{{ route('admin.pesan.balas', $pesan) }}" method="POST">
                @csrf
                <button type="submit" class="admin-btn admin-btn-primary w-full">
                    <x-admin.icon name="reply" width="16" height="16" />
                    Balas lewat email
                </button>
            </form>
            <p class="pb-2 text-xs text-slate">Membuka aplikasi email Anda dan menandai pesan ini sudah dibalas.</p>

            @unless ($pesan->status === StatusPesan::BelumDibaca)
                <form action="{{ route('admin.pesan.status', $pesan) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ StatusPesan::BelumDibaca->value }}">
                    <button type="submit" class="admin-btn admin-btn-secondary w-full">
                        <x-admin.icon name="mail" width="16" height="16" />
                        Tandai belum dibaca
                    </button>
                </form>
            @endunless

            <form action="{{ route('admin.pesan.status', $pesan) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ ($diarsipkan ? StatusPesan::Dibaca : StatusPesan::Diarsipkan)->value }}">
                <button type="submit" class="admin-btn admin-btn-secondary w-full">
                    <x-admin.icon name="archive" width="16" height="16" />
                    {{ $diarsipkan ? 'Keluarkan dari arsip' : 'Arsipkan' }}
                </button>
            </form>

            @can('admin')
                <form action="{{ route('admin.pesan.destroy', $pesan) }}" method="POST" data-confirm="Hapus pesan dari {{ $pesan->nama }}? Pesan yang dihapus tidak bisa dikembalikan.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn w-full border border-[#f3c4c7] bg-white text-[#c0262f] hover:bg-[#fff5f5] focus-visible:ring-[#c0262f]/20">
                        <x-admin.icon name="trash" width="16" height="16" />
                        Hapus pesan
                    </button>
                </form>
            @endcan
        </aside>
    </div>
</x-layouts.admin>
