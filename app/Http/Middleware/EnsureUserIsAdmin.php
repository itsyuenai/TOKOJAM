<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('filament.admin.auth.login'));
        }

        abort_unless((bool) $user->is_admin, Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
