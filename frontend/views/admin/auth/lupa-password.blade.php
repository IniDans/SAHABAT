<x-layouts.auth title="Lupa Password" heading="Lupa password" description="Masukkan email akun admin. Kami akan mengirim tautan untuk membuat password baru.">
    <form action="{{ route('password.email') }}" method="POST" class="mt-9 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm text-[#1e2328]">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus maxlength="255" autocomplete="email" placeholder="admin@contoh.com" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="auth-input mt-1.5">
            @error('email')
                <p id="email-error" class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-btn">Kirim tautan reset</button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-slate hover:text-brand-red">Kembali ke login</a>
        </p>
    </form>
</x-layouts.auth>
