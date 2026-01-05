<?php

namespace App\Http\Middleware;

use App\Enums\UserRoleEnum;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LbrOrKplOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $hasRole = Auth::user()->roles->whereIn('code', [
            UserRoleEnum::LAB_HEAD->value,
            UserRoleEnum::LABORAN->value,
        ])->isNotEmpty();

        if (!$hasRole) {
            abort(code: 404);
        }

        return $next($request);
    }
}
