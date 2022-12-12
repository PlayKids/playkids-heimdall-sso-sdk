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
                    throw new Exception('Permission denied', 422);
                }
                if(isset($role) && !$heimdallService->accessTokenHasRole($role)) {
                    throw new Exception('Permission denied', 422);
                }

                $heimdallService->setHeimdallUser();
            } else {
                throw new Exception('Token is missing', 422);
            }
        } catch (Exception $exception) {
            return response()->json([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'status' => 'error'
            ]);
        }

        return $next($request);
    }
}
