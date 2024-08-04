<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class EnsureApiTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }

    /**
     * Authenticate the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function authenticate(Request $request)
    {
        if (!auth()->check()) {
            throw new AuthenticationException('Unauthenticated.');
        }
    }
}
