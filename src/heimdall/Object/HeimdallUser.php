<?php

namespace Heimdall\Object;

use Illuminate\Contracts\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string id
 * @property string externalId
 * @property string email
 * @property string firstName
 * @property string lastName
 * @property string accessToken
 * @property int expiresIn
 * @property string refreshToken
 * @property int refreshExpiresIn
 * @property string idToken
 * @property object roles
 */
class HeimdallUser implements Authenticatable, JWTSubject
{

    /**
     * HeimdallUser constructor.
     * @param string $id
     * @param string $externalId
     * @param string $email
     * @param ?string $firstName
     * @param ?string $lastName
     * @param string $accessToken
     * @param int $expiresIn
     * @param ?string $refreshToken
     * @param ?int $refreshExpiresIn
     * @param ?string $idToken
     * @param ?object $roles
     */
    public function __construct(
        string $id,
        string $externalId,
        string $email,
        ?string $firstName,
        ?string $lastName,
        string $accessToken,
        int $expiresIn,
        ?string $refreshToken,
        ?int $refreshExpiresIn,
        ?string $idToken,
        ?object $roles
    )
    {
        $this->id = $id;
        $this->externalId = $externalId;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->accessToken = $accessToken;
        $this->expiresIn = $expiresIn;
        $this->refreshToken = $refreshToken;
        $this->refreshExpiresIn = $refreshExpiresIn;
        $this->idToken = $idToken;
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