<x-guest-layout>
    <div class="login-container">
        <div class="login-header">
            <h1>Reset Password</h1>
            <p>Masukkan kata sandi baru Anda.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-5">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-5">
                <label for="password">Password Baru</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-5">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; background: #2563eb; color: #fff; border-radius: 6px; font-weight: 600;">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
