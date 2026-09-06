<x-guest-layout>
    <!-- Background Animation Canvas -->
    <canvas id="warehouse-bg"></canvas>

    <!-- Register Form -->
    <div class="login-container" style="max-width: 500px; padding: 2rem 2.5rem;">
        <div class="login-header" style="margin-bottom: 1.5rem;">
            <h1>Registrasi Akun</h1>
            <p>Daftar untuk mengakses sistem ERP Lite Hybrid PJU</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <label for="name">Nama Lengkap</label>
                <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Username -->
            <div class="mb-4">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" :value="old('username')" required autocomplete="username">
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" :value="old('email')" required autocomplete="email">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Role Selection -->
            <div class="mb-4">
                <label for="role_id">Jabatan / Role</label>
                <select id="role_id" name="role_id" required class="w-full" style="padding: 0.75rem 0.9rem; border-radius: 0.5rem; background-color: #f8fafc; border: 1px solid #cbd5e1; font-size: 0.95rem; color: #1e293b;">
                    <option value="" disabled selected>-- Pilih Jabatan --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ ucwords($role->role) }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mb-5">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit">DAFTAR</button>
            
            <div class="mt-4 text-center">
                <a class="text-sm text-gray-600 hover:text-gray-900" style="text-decoration: underline;" href="{{ route('login') }}">
                    {{ __('Sudah punya akun? Login di sini') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Background Particle Animation Script -->
    <script>
        const canvas = document.getElementById("warehouse-bg");
        const ctx = canvas.getContext("2d");
        let particles = [];

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        window.addEventListener("resize", resize);
        resize();

        class Particle {
            constructor() {
                this.reset();
            }

            reset() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 3 + 1;
                this.speedX = Math.random() * 1 - 0.5;
                this.speedY = Math.random() * 1 - 0.5;
                this.alpha = Math.random() * 0.6 + 0.2;
                this.color = Math.random() > 0.5 ? "#6366f1" : "#14b8a6";
            }

            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.x < 0 || this.x > canvas.width || this.y < 0 || this.y > canvas.height) {
                    this.reset();
                }
            }

            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.color + "40";
                ctx.fill();
            }
        }

        function init() {
            particles = [];
            for (let i = 0; i < 70; i++) {
                particles.push(new Particle());
            }
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.globalAlpha = 0.8;
            particles.forEach(p => {
                p.update();
                p.draw();
            });
            requestAnimationFrame(animate);
        }

        init();
        animate();
    </script>
    <style>

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', 'Poppins', sans-serif;
            background: linear-gradient(180deg, #f9fafb 0%, #e5e7eb 100%);
            overflow: hidden;
            position: relative;
            color: #1e293b;
        }

        /* === Background Canvas for Warehouse Animation === */
        #warehouse-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
        }

        /* === Container === */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.9);
            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.08),
                inset 0 0 0 1px rgba(209, 213, 219, 0.5);
            backdrop-filter: blur(12px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: slideFade 0.9s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        .login-container::-webkit-scrollbar {
            width: 8px;
        }
        .login-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .login-container::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
        }

        .login-container:hover {
            transform: translateY(-4px);
            box-shadow:
                0 12px 40px rgba(99, 102, 241, 0.15),
                inset 0 0 0 1px rgba(99, 102, 241, 0.2);
        }

        /* === Header === */
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(90deg, #6366f1, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .login-header p {
            color: #64748b;
            font-size: 0.9rem;
            text-align: center;
        }

        /* === Labels === */
        label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #475569;
            display: block;
            margin-bottom: 0.3rem;
        }

        /* === Inputs === */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.75rem 0.9rem;
            border-radius: 0.5rem;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            color: #1e293b;
            transition: all 0.25s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        /* === Button === */
        button[type="submit"] {
            width: 100%;
            padding: 0.8rem;
            margin-top: 1rem;
            border-radius: 0.6rem;
            border: none;
            background: linear-gradient(90deg, #6366f1, #14b8a6);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.3px;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.25);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35);
        }

        /* === Animations === */
        @keyframes slideFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* === Responsive === */
        @media (max-width: 640px) {
            .login-container {
                padding: 2rem 1.5rem !important;
                margin: 1rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</x-guest-layout>
