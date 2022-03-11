<?php

namespace Heimdall\Service;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
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
     * @throws Exception
     */
    public function decodeAccessToken(string $algorithm = "RS256")
    {
        $heimdallPublicKey = getenv('HEIMDALL_PUBLIC_KEY');

        if(empty($heimdallPublicKey)){
            $heimdallPublicKey = $this->getPublicHeimdallKey();
        }

        $key = "-----BEGIN PUBLIC KEY-----" . PHP_EOL .
            $heimdallPublicKey . PHP_EOL .
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

    public function request(
        string $service,
        array $data = null,
        string $method = 'GET',
        array $options = null
    )
    {
        $client = new Client();

        $uri = getenv('HEIMDALL_API_URI') . $service;

        $response = $client->request($method, $uri, $options??[
                'headers' => ['Authorization' => "Bearer " . $this->ACCESS_TOKEN, 'Content-Type' => 'application/json'],
                'http_errors' => false,
                'body' => isset($data) ? json_encode($data) : null
            ]);

        if ($response->getStatusCode() !== 500) {
            return json_decode($response->getBody()->getContents());
        }

        throw new Exception($response->getBody()->getContents() ?? 'Error on send request to SSO Interface', $response->getStatusCode() ?? 500);
    }

    /**
     * @param array $loginData
     * @return mixed|string
     * @throws Exception
     */
    public function attemptUserLogin(array $loginData)
    {
        $data = [
            'username' => $loginData['username'],
            'password' => $loginData['password'],
            'project'  => $this->CLIENT_ID
        ];

        $loginResponse = $this->request('auth/login', $loginData, 'POST', [
            'headers' => ['Content-Type' => 'application/json'],
            'http_errors' => false,
            'body' => isset($data) ? json_encode($data) : null
        ]);

        if(!isset($loginResponse->status) || $loginResponse->status != 200) {
            throw new Exception($loginResponse->error ?? 'Attempt Login Error', $loginResponse->status ?? 500);
        }

        return $loginResponse->body??"";
    }

    private function getPublicHeimdallKey()
    {
        $response = $this->request('config/public_key/project/' . $this->CLIENT_ID, null, 'GET', [
            'headers' => ['Content-Type' => 'application/json'],
            'http_errors' => false
        ]);

        if(!isset($response->status) || $response->status != 200) {
            throw new Exception($response->error ?? 'Attempt Login Error', $response->status ?? 500);
        }

        return $response->body->public_key??"";
    }
}