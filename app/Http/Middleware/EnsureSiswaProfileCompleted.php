<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiswaProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $siswa = $request->user()?->siswa;

        if (! $siswa || ! $siswa->isProfileComplete()) {
            return redirect()
                ->route('siswa.profile')
                ->with('profile_required_message', 'Lengkapi profil terlebih dahulu sebelum memilih DUDI.');
        }

        return $next($request);
    }
}
