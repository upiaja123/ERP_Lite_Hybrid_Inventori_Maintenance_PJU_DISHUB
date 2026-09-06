<x-guest-layout>
    <div class="login-container">
        <div class="login-header">
            <h1>Konfirmasi Password</h1>
            <p>Ini adalah area aman pada aplikasi. Harap konfirmasi kata sandi Anda sebelum melanjutkan.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="mb-5">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; background: #2563eb; color: #fff; border-radius: 6px; font-weight: 600;">
                    Konfirmasi
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
