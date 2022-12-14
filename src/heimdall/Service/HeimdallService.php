<?php

namespace Heimdall\Service;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use Heimdall\Object\HeimdallUser;
use Illuminate\Support\Facades\Auth;
use stdClass;

class HeimdallService
{

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
     * @return HeimdallUser|string
     * @throws Exception
     */
    public function attemptUserLogin(array $loginData)
    {
        $data = [
            'username' => $loginData['username'],
            'password' => $loginData['password'],
            'project'  => $this->CLIENT_ID
        ];

        if($this->CLIENT_ID == "leiturinha-admin"){
            $service = "auth/login/leiturinha-admin";
        }else{
            $service = "auth/login";
        }

        $loginResponse = $this->request($service, $loginData, 'POST', [
            'headers' => ['Content-Type' => 'application/json'],
            'http_errors' => false,
            'body' => isset($data) ? json_encode($data) : null
        ]);

        if(!isset($loginResponse->status) || $loginResponse->status != 200) {
            throw new Exception($loginResponse->error ?? 'Attempt Login Error', $loginResponse->status ?? 500);
        }

        return new HeimdallUser(
                $loginResponse->body->id,
                $loginResponse->body->externalId,
                $loginResponse->body->email,
                $loginResponse->body->firstName,
                $loginResponse->body->lastName,
                $loginResponse->body->accessToken,
                $loginResponse->body->expiresIn,
                $loginResponse->body->refreshToken,
                $loginResponse->body->refreshExpiresIn,
                $loginResponse->body->idToken,
                $loginResponse->body->roles
        )??"";
    }

    /**
     * @return HeimdallUser|string
     * @throws Exception
     */
    public function refreshAccessToken()
    {

        $loginResponse = $this->request("auth/token/refresh/project/".$this->CLIENT_ID, null, 'GET', [
            'headers' => ['Authorization' => "Bearer " . $this->ACCESS_TOKEN, 'Content-Type' => 'application/json'],
            'http_errors' => false,
            'body' => null
        ]);

        if(!isset($loginResponse->status) || $loginResponse->status != 200) {
            throw new Exception($loginResponse->error ?? 'Refresh Token Error', $loginResponse->status ?? 500);
        }

        return new HeimdallUser(
                $loginResponse->body->id,
                $loginResponse->body->externalId,
                $loginResponse->body->email,
                $loginResponse->body->firstName,
                $loginResponse->body->lastName,
                $loginResponse->body->accessToken,
                $loginResponse->body->expiresIn,
                $loginResponse->body->refreshToken,
                $loginResponse->body->refreshExpiresIn,
                $loginResponse->body->idToken,
                $loginResponse->body->roles
            )??"";
    }

    public function getUserInfo(string $externalSSOID) {
        $response = $this->request('auth/user/' . $externalSSOID . '/project/' . $this->CLIENT_ID, null, 'GET', [
            'headers' => ['Authorization' => "Bearer " . $this->ACCESS_TOKEN, 'Content-Type' => 'application/json'],
            'http_errors' => false,
            'body' => null
        ]);

        if(!isset($response->status) || $response->status != 200) {
            throw new Exception($response->error ?? 'Get User Info Error', $response->status ?? 500);
        }

        return $response->body??"";
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

    public function setHeimdallUser()
    {
        try {
            $decodedAccessToken = $this->decodeAccessToken();

            if($this->CLIENT_ID == "leiturinha-admin"){

                Auth::setUser(new HeimdallUser(
                    $decodedAccessToken->user->id,
                    $decodedAccessToken->user->id,
                    $decodedAccessToken->user->email,
                    $decodedAccessToken->user->name,
                    "",
                    $this->getAccessToken(),
                    $decodedAccessToken->exp,
                    "",
                    0,
                    "",
                    (object)[]
                ));

            }else{
                $userInfo = $this->getUserInfo($decodedAccessToken->sub);

                Auth::setUser(new HeimdallUser(
                    $userInfo->id,
                    $userInfo->externalId,
                    $userInfo->email,
                    $userInfo->firstName,
                    $userInfo->lastName,
                    $this->getAccessToken(),
                    $decodedAccessToken->exp,
                    "",
                    0,
                    "",
                    (object)[]
                ));
            }


        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}