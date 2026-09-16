<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        // Jika user belum login, redirect ke halaman login
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cek apakah user aktif
        if (!$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif atau ditangguhkan.');
        }

        // Cek jika user punya salah satu role dari parameter
        if ($user->hasRole($roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
