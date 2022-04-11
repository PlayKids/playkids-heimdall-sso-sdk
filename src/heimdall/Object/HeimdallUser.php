<?php

namespace Heimdall\Object;

use Illuminate\Contracts\Auth\Authenticatable;

class HeimdallUser implements Authenticatable
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
        string $refreshToken
    )
    {
        $this->uid = $uid;
        $this->name = $name;
        $this->email = $email;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
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
}