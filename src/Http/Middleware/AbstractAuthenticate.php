<?php

namespace KieranFYI\Services\Core\Http\Middleware;

use Exception;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use KieranFYI\Services\Core\Interfaces\ServiceInterface;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Services\EchoService;
use KieranFYI\Services\Core\Services\EventService;
use KieranFYI\Services\Core\Services\MaintenanceService;
use KieranFYI\Services\Core\Services\QueryService;
use KieranFYI\Services\Core\Services\RegistrationService;

abstract class AbstractAuthenticate implements AuthenticatesRequests
{
    /**
     * The authentication factory instance.
     *
     * @var Auth
     */
    protected Auth $auth;

    /**
     * @var string
     */
    protected string $guard;

    /**
     * @var array
     */
    protected array $allowed;

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
     * @return Response
     * @throws Exception
     */
    public function handle(Request $request): Response
    {
        if (!config('service.enabled')) {
            abort(501);
        }

        /** @var TokenGuard $guard */
        $guard = $this->auth->guard($this->guard);
        $guard->setRequest($request);
        if (!$guard->check()) {
            abort(401);
        }
        $this->auth->shouldUse($this->guard);

        /** @var Service $service */
        $service = $guard->user();
        //$service->update(['last_used_at' => Carbon::now()]);

        $content = $this->decryptContent($service, $request);
        /** @var ServiceInterface $content */
        $response = $content->execute();
        if (!is_null($response)) {
            $content = $response;
        }

        return response($this->encryptContent($service, $content));
    }

    /**
     * @param Service $service
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function decryptContent(Service $service, Request $request): mixed
    {
        $serviceClass = $request->json('service');
        if (!in_array($serviceClass, $this->allowed)) {
            abort(401);
        }

        $content = $request->json('content');
        if (empty($content)) {
            abort(401);
        }

        $content = base64_decode($content);
        if (config('service.encrypt')) {
            if (strlen($content) < 16) {
                abort(401);
            }
            if (!isset($service->symmetric_key)) {
                openssl_private_decrypt($content, $decrypted, $service->asymmetric_key);
                if ($decrypted === $content || $decrypted === false) {
                    abort(401);
                }
                $content = $decrypted;
            } else {
                $key = substr($content, 0, 16);
                $encrypted = substr($content, 16);
                $content = openssl_decrypt($encrypted, config('app.cipher'), $service->symmetric_key, iv: $key);
                if ($encrypted === $content || $content === false) {
                    abort(401);
                }
            }
        }

        preg_match_all('/O:\d+:\"(.*?)\"/', $content, $matches);
        if (!Str::is($serviceClass, $matches[1][0] ?? null)) {
            abort(401);
        }

        return unserialize($content);
    }

    /**
     * @param Service $service
     * @param ServiceInterface $content
     * @return string
     * @throws Exception
     */
    public function encryptContent(Service $service, mixed $content): string
    {
        $content = serialize($content);

        if (config('service.encrypt')) {
            $iv = random_bytes(16);
            $content = $iv . openssl_encrypt($content, config('app.cipher'), $service->symmetric_key, iv: $iv);
        }

        return base64_encode($content);
    }
}