{{-- Kaki tabel admin: ringkasan jumlah + tombol halaman. Pakai: $paginator->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'donasi']) --}}
@if ($paginator->total() > 0)
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#e5e5e5] px-5 py-3">
        <p class="text-[13px] text-slate">
            Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }} dari {{ $paginator->total() }} {{ $satuan ?? 'data' }}
        </p>

        @if ($paginator->hasPages())
            <nav class="flex items-center gap-1.5" aria-label="Navigasi halaman">
                @php
                    $tombol = 'flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-[13px] font-semibold tabular-nums';
                @endphp

                @if ($paginator->onFirstPage())
                    <span class="{{ $tombol }} border-[#e5e5e5] text-slate-light" aria-disabled="true" aria-label="Halaman sebelumnya">&lsaquo;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $tombol }} border-[#e5e5e5] bg-white hover:border-footer" aria-label="Halaman sebelumnya">&lsaquo;</a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="{{ $tombol }} border-[#e5e5e5] text-slate" aria-hidden="true">{{ $element }}</span>
                    @else
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $tombol }} border-footer bg-footer text-white" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $tombol }} border-[#e5e5e5] bg-white hover:border-footer" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $tombol }} border-[#e5e5e5] bg-white hover:border-footer" aria-label="Halaman berikutnya">&rsaquo;</a>
                @else
                    <span class="{{ $tombol }} border-[#e5e5e5] text-slate-light" aria-disabled="true" aria-label="Halaman berikutnya">&rsaquo;</span>
                @endif
            </nav>
        @endif
    </div>
@endif
