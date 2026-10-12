<x-layouts.auth title="Login Admin" heading="Login Admin" description="Masuk untuk mengelola data panti.">
    <form action="{{ route('login.store') }}" method="POST" class="mt-9 space-y-5">
        @csrf

        <div>
            <label for="login" class="block text-sm text-[#1e2328]">Email atau username</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus maxlength="255" autocomplete="username" placeholder="admin@contoh.com" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror class="auth-input mt-1.5">
            @error('login')
                <p id="login-error" class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
            @enderror
        </div>

        <x-auth.password-input name="password" label="Password" />

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-[#1e2328]">
                <input type="checkbox" name="remember" class="size-4 rounded border-field accent-brand-red">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="text-slate hover:text-brand-red">Lupa password?</a>
        </div>

        <button type="submit" class="auth-btn">Masuk</button>
    </form>
</x-layouts.auth>
