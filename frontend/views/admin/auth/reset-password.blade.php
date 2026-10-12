<x-layouts.auth title="Atur Ulang Password" heading="Buat password baru" description="Password minimal 8 karakter, berisi huruf besar, huruf kecil, dan angka.">
    <form action="{{ route('password.update') }}" method="POST" class="mt-9 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm text-[#1e2328]">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required maxlength="255" autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="auth-input mt-1.5">
            @error('email')
                <p id="email-error" class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
            @enderror
        </div>

        <x-auth.password-input name="password" label="Password baru" autocomplete="new-password" placeholder="Password baru" autofocus />
        <x-auth.password-input name="password_confirmation" label="Ulangi password baru" autocomplete="new-password" placeholder="Ulangi password baru" />

        <button type="submit" class="auth-btn">Simpan password baru</button>
    </form>
</x-layouts.auth>
