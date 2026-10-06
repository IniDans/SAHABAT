@php
    use App\Enums\Role;
@endphp

<x-layouts.admin title="Kelola Akun">
    <x-admin.page-heading title="Kelola akun" description="Akun yang bisa masuk ke panel admin. Admin bisa mengelola akun dan menghapus data; pengurus hanya menambah dan mengubah data.">
        <a href="{{ route('admin.akun.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Tambah akun
        </a>
    </x-admin.page-heading>

    @error('akun')
        <p class="mb-5 rounded-lg border border-[#f5c2c5] bg-[#fde5e5] px-4 py-3 text-[13px] text-[#c0262f]" role="alert">{{ $message }}</p>
    @enderror

    <form method="GET" class="flex flex-wrap items-center gap-2" role="search">
        <label class="sr-only" for="cari-akun">Cari nama atau email</label>
        <input id="cari-akun" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email" class="admin-input w-full sm:w-[280px]">
        <x-admin.filter-select name="role" label="Semua role" :options="collect(Role::cases())->mapWithKeys(fn ($case) => [$case->value => ucfirst($case->value)])" />
        <x-admin.filter-select name="status" label="Semua status" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" />
        @if (request()->hasAny(['search', 'role', 'status']))
            <a href="{{ route('admin.akun.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Email</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Role</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($akun as $item)
                        <tr @class(['text-slate' => ! $item->is_active])>
                            <td class="px-4 py-3 font-semibold">
                                {{ $item->name }}
                                @if ($item->is(auth()->user()))
                                    <span class="ml-1 text-xs font-normal text-slate">(Anda)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $item->email }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$item->isAdmin() ? 'green' : 'gray'">{{ ucfirst($item->role->value) }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$item->is_active ? 'green' : 'red'">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.akun.edit', $item) }}" class="admin-icon-btn" aria-label="Ubah akun {{ $item->name }}" title="Ubah">
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </a>
                                    @unless ($item->is(auth()->user()))
                                        <x-admin.delete-button :action="route('admin.akun.destroy', $item)" :confirm="'Hapus akun '.$item->name.'?'" :label="'Hapus akun '.$item->name" />
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate">Tidak ada akun yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $akun->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'akun']) }}
    </div>
</x-layouts.admin>
