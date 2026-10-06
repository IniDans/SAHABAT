<x-layouts.admin title="Akun Saya">
    <x-admin.page-heading title="Akun saya" :description="'Masuk sebagai '.ucfirst($akun->role->value).'.'" />

    <div class="grid max-w-4xl gap-5 lg:grid-cols-2">
        <form action="{{ route('admin.akun-saya.update') }}" method="POST" class="admin-card space-y-5 p-5">
            @csrf
            @method('PUT')

            <h2 class="text-base font-semibold">Data akun</h2>

            <div>
                <label for="name" class="admin-label">Nama (username) <span class="text-[#dc3c45]" aria-hidden="true">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $akun->name) }}" required maxlength="255" autocomplete="username" class="admin-input" @error('name', 'profil') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name', 'profil')
                    <p id="name-error" class="mt-1 text-xs text-[#c0262f]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="admin-label">Email <span class="text-[#dc3c45]" aria-hidden="true">*</span></label>
                <input id="email" name="email" type="email" value="{{ old('email', $akun->email) }}" required maxlength="255" autocomplete="email" class="admin-input" @error('email', 'profil') aria-invalid="true" aria-describedby="email-error" @enderror>
                <p class="mt-1 text-xs text-slate">Dipakai untuk login dan menerima tautan reset password.</p>
                @error('email', 'profil')
                    <p id="email-error" class="mt-1 text-xs text-[#c0262f]">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t border-[#e5e5e5] pt-5">
                <button type="submit" class="admin-btn admin-btn-primary">Simpan data akun</button>
            </div>
        </form>

        <form action="{{ route('admin.akun-saya.password') }}" method="POST" class="admin-card space-y-5 p-5">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-base font-semibold">Ganti password</h2>
                <p class="mt-1 text-xs text-slate">Setelah diganti, perangkat lain yang memakai akun ini harus login ulang.</p>
            </div>

            @foreach ([
                'current_password' => ['Password saat ini', 'current-password', null],
                'password' => ['Password baru', 'new-password', 'Minimal 8 karakter, huruf besar, huruf kecil, dan angka.'],
                'password_confirmation' => ['Ulangi password baru', 'new-password', null],
            ] as $nama => [$label, $autocomplete, $hint])
                <div>
                    <label for="{{ $nama }}" class="admin-label">{{ $label }} <span class="text-[#dc3c45]" aria-hidden="true">*</span></label>
                    <input id="{{ $nama }}" name="{{ $nama }}" type="password" required maxlength="255" autocomplete="{{ $autocomplete }}" class="admin-input" @error($nama, 'password') aria-invalid="true" aria-describedby="{{ $nama }}-error" @enderror>
                    @if ($hint)
                        <p class="mt-1 text-xs text-slate">{{ $hint }}</p>
                    @endif
                    @error($nama, 'password')
                        <p id="{{ $nama }}-error" class="mt-1 text-xs text-[#c0262f]">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="border-t border-[#e5e5e5] pt-5">
                <button type="submit" class="admin-btn admin-btn-primary">Ganti password</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
