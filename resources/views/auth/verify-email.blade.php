<x-guest-layout>
    <div class="login-container">
        <div class="login-header">
            <h1>Verifikasi Email</h1>
            <p>Terima kasih telah mendaftar! Sebelum memulai, harap verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600" style="color: #16a34a; margin-bottom: 15px;">
                Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <div>
                    <button type="submit" class="btn-primary" style="padding: 10px 15px; background: #2563eb; color: #fff; border-radius: 6px; font-weight: 600;">
                        Kirim Ulang Email Verifikasi
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}" style="margin-top: 15px;">
                @csrf
                <button type="submit" style="color: #ef4444; background: none; border: none; cursor: pointer; text-decoration: underline;">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
