<?php

namespace Heimdall\Middleware;

use Closure;
use Exception;
use Heimdall\Service\HeimdallService;
use Illuminate\Http\Request;

class AuthenticateHeimdall
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string|null
     */
    protected function redirectTo($request): ?string
    {
        return (!$request->expectsJson()) ? '/error' : null;
    }

    public function handle($request, Closure $next, string $role = null)
    {
        try {
            $token = request()->bearerToken();

            if(isset($token)) {

                $heimdallService = new HeimdallService(env("HEIMDALL_PROJECT"));
                $heimdallService->setAccessToken($token);

                if(!$heimdallService->isValidAccessToken()){
                    throw new Exception('Permission denied', 401);
                }
                if(isset($role) && !$heimdallService->accessTokenHasRole($role)) {
                    throw new Exception('Permission denied', 401);
                }

                $heimdallService->setHeimdallUser();
            } else {
                throw new Exception('Token is missing', 401);
            }
        } catch (Exception $exception) {
            return response()->json([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'status' => 'error'
            ], 401);
        }

        return $next($request);
    }
}
