<x-guest-layout>
    <div class="login-container">
        <div class="login-header">
            <h1>Lupa Password</h1>
            <p>Masukkan email Anda untuk menerima link reset kata sandi.</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-5">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" :value="old('email')" required autofocus>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; background: #2563eb; color: #fff; border-radius: 6px; font-weight: 600;">
                    Kirim Link Reset Password
                </button>
            </div>
            <div style="text-align: center; margin-top: 15px;">
                <a href="{{ route('login') }}" style="color: #2563eb; font-size: 0.9rem;">Kembali ke Login</a>
            </div>
        </form>
    </div>
</x-guest-layout>
