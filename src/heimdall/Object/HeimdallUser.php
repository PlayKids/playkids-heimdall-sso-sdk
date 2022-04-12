<?php

namespace Heimdall\Object;

use Illuminate\Contracts\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class HeimdallUser implements Authenticatable, JWTSubject
{

    var $uid;
    var $name;
    var $email;
    var $accessToken;
    var $refreshToken;
    var $expireToken;
    var $roles;

    public function __construct(
        string $uid,
        string $name,
        string $email,
        string $accessToken,
        string $refreshToken,
        array $roles
    )
    {
        $this->uid = $uid;
        $this->name = $name;
        $this->email = $email;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->roles = $roles;
    }

    public function getAuthIdentifierName()
    {
       return 'email';
    }

    public function getAuthIdentifier()
    {
        return $this->email;
    }

    public function getAuthPassword()
    {
        // TODO: Implement getAuthPassword() method.
    }

    public function getRememberToken()
    {
        return $this->refreshToken;
    }

    public function setRememberToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getRememberTokenName()
    {
        return 'refreshToken';
    }

    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
    }

    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
    }
}