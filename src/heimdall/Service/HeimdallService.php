<?php

namespace Heimdall\Service;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Heimdall\Object\Heimdall;
use stdClass;

class HeimdallService
{

    /**
     * @var Heimdall
     */
    private $heimdall;
    private $CLIENT_ID;
    private $ACCESS_TOKEN;

    /**
     * HeimdallService constructor.
     * @param string $project
     */
    public function __construct(string $project)
    {
        $this->CLIENT_ID = $project;
    }

    /**
     * @param Heimdall $heimdall
     */
    public function setClient(Heimdall $heimdall) {
        $this->heimdall = $heimdall;
    }

    public function getClient()
    {
        return $this->heimdall;
    }

    public function setAccessToken(string $token)
    {
        $this->ACCESS_TOKEN = $token;
    }

    public function getAccessToken()
    {
        return $this->ACCESS_TOKEN;
    }

    /**
     * @param string $algorithm
     * @return bool
     * @throws Exception
     */
    public function isValidAccessToken(string $algorithm = "RS256")
    {
        try {
            $tokenDecoded = $this->decodeAccessToken($algorithm);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 400);
        }

        if(!isset($tokenDecoded->exp)) {
            throw new Exception('Invalid access token', 500);
        }

        if(time() > $tokenDecoded->exp) {
            throw new Exception('Expired access token', 500);
        }

        return true;
    }

    /**
     * @param string $algorithm
     * @return object
     */
    public function decodeAccessToken(string $algorithm = "RS256")
    {
        $key = "-----BEGIN PUBLIC KEY-----" . PHP_EOL .
            getenv('KEYCLOAK_REALM_PUBLIC_KEY') . PHP_EOL .
            "-----END PUBLIC KEY-----";

        return JWT::decode($this->ACCESS_TOKEN, new Key($key, $algorithm));
    }

    /**
     * @param string $role
     * @return bool
     * @throws Exception
     */
    public function accessTokenHasRole(string $role)
    {
        $decodedAccessToken = $this->decodeAccessToken();

        if(!isset($decodedAccessToken->resource_access)) {
            throw new Exception("Permissions not found", 403);
        }

        if(!isset($decodedAccessToken->resource_access->{$this->CLIENT_ID})) {
            throw new Exception("Client permissions not found", 403);
        }

        return in_array($role,$decodedAccessToken->resource_access->{$this->CLIENT_ID}->roles);
    }

    public function getRoles(stdClass $decodedAccessToken)
    {
        $roles = null;

        if(!isset($decodedAccessToken->resource_access)) {
            return null;
        }

        foreach ($decodedAccessToken->resource_access as $client => $clientRoles) {
            $roles[$client] = $clientRoles->roles??[];
        }

        return $roles;
    }
}