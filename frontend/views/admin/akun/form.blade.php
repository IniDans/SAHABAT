@php
    use App\Enums\Role;

    $isEdit = $akun->exists;
@endphp

<x-layouts.admin :title="$isEdit ? 'Ubah Akun' : 'Tambah Akun'">
    <a href="{{ route('admin.akun.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-[13px] text-slate hover:text-[#1e2328]">
        <x-admin.icon name="arrow-left" width="14" height="14" />
        Kembali ke daftar akun
    </a>

    <x-admin.page-heading :title="$isEdit ? 'Ubah akun' : 'Tambah akun'" />

    <form action="{{ $isEdit ? route('admin.akun.update', $akun) : route('admin.akun.store') }}" method="POST" class="admin-card max-w-2xl space-y-5 p-5">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field label="Nama (username)" name="name" required>
                <input id="name" name="name" type="text" value="{{ old('name', $akun->name) }}" required maxlength="255" autocomplete="off" class="admin-input" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            </x-admin.field>

            <x-admin.field label="Email" name="email" required>
                <input id="email" name="email" type="email" value="{{ old('email', $akun->email) }}" required maxlength="255" autocomplete="off" class="admin-input" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            </x-admin.field>
        </div>

        <x-admin.field label="Role" name="role" required hint="Admin bisa mengelola akun dan menghapus data. Pengurus hanya bisa menambah dan mengubah data.">
            <select id="role" name="role" required class="admin-input" @error('role') aria-invalid="true" aria-describedby="role-error" @enderror>
                @foreach (Role::cases() as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $akun->role?->value) === $role->value)>{{ ucfirst($role->value) }}</option>
                @endforeach
            </select>
        </x-admin.field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field :label="$isEdit ? 'Password baru' : 'Password'" name="password" :required="! $isEdit" :hint="($isEdit ? 'Kosongkan bila tidak diganti. ' : '').'Minimal 8 karakter, huruf besar, huruf kecil, dan angka.'">
                <input id="password" name="password" type="password" @required(! $isEdit) maxlength="255" autocomplete="new-password" class="admin-input" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
            </x-admin.field>

            <x-admin.field label="Ulangi password" name="password_confirmation" :required="! $isEdit">
                <input id="password_confirmation" name="password_confirmation" type="password" @required(! $isEdit) maxlength="255" autocomplete="new-password" class="admin-input">
            </x-admin.field>
        </div>

        <div>
            <label class="flex items-center gap-2 text-[13px]">
                <input type="checkbox" name="is_active" value="1" class="size-4 accent-footer" @checked(old('is_active', $akun->is_active))>
                Akun aktif (bisa login)
            </label>
            @error('is_active')
                <p class="mt-1 text-xs text-[#c0262f]">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-2 border-t border-[#e5e5e5] pt-5">
            <button type="submit" class="admin-btn admin-btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Simpan akun' }}</button>
            <a href="{{ route('admin.akun.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
        </div>
    </form>
</x-layouts.admin>
