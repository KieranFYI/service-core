<?php

namespace KieranFYI\Services\Core\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Authenticate implements AuthenticatesRequests
{
    /**
     * The authentication factory instance.
     *
     * @var Auth
     */
    protected Auth $auth;

    /**
     * Create a new middleware instance.
     *
     * @param Auth $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string[] ...$guards
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guard = $this->auth->guard('services');
        if (!$guard->check() || $guard->user()->name !== $request->userAgent()) {
            abort(401);
        }
        $this->auth->shouldUse('services');

        $guard->user()->update(['last_used_at' => Carbon::now()]);

        return $next($request);
    }
}
