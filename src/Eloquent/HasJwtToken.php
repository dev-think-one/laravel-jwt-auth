<?php

namespace JWTAuth\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Str;
use JWTAuth\JWTManager;
use JWTAuth\JWTPayload;

trait HasJwtToken
{
    /**
     * JWT token object.
     *
     * @var JWTManager|null
     */
    protected ?JWTManager $jwtToken = null;


    /**
     * Payload key name.
     *
     * @return mixed
     */
    public function getJwtPayloadIdentifierKey()
    {
        return $this->getJwtAuthIdentifierKey();
    }

    /**
     * Model token key name.
     *
     * @return mixed
     */
    public function getJwtAuthIdentifierKey()
    {
        return $this->getAuthIdentifierName();
    }

    /**
     * Get stored JWT tokens for current model.
     *
     * @return mixed
     */
    public function storedJwtTokens()
    {
        return $this->morphMany(config('jwt-auth.models.tokens', StoredJwtToken::class), 'tokenable');
    }

    /**
     * Check abilities for current JWT token.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function jwtTokenCan(string $ability): bool
    {
        return $this->currentJwtToken() && $this->currentJwtToken()->payload()->can($ability);
    }

    /**
     * Get loaded JWT token.
     *
     * @return JWTManager|null
     */
    public function currentJwtToken(): ?JWTManager
    {
        return $this->jwtToken;
    }

    /**
     * Set JWT token.
     *
     * @param JWTManager $jwt
     *
     * @return self
     */
    public function withJwtToken(JWTManager $jwt): self
    {
        $this->jwtToken = $jwt;

        return $this;
    }

    /**
     * Generate payload.
     *
     * @param string $name
     * @param array|string[] $abilities
     *
     * @return JWTPayload
     */
    public function createPayload(string $name, array $abilities = [ '*' ]): JWTPayload
    {
        $storedToken = $this->storedJwtTokens()->create([
            'name'      => $name,
            'jti'       => hash(config('jwt-auth.token.jti.hash_algo', 'sha256'), Str::random(40)),
            'abilities' => $abilities,
            'exp'       => Carbon::now()->addSeconds(config('jwt-auth.token.expiration', 3600 * 24))->timestamp,
        ]);
        $payload = ( new JWTPayload([
            $this->getJwtPayloadIdentifierKey() => $this->{$this->getJwtAuthIdentifierKey()},
            'exp'                               => $storedToken->exp,
            'jti'                               => $storedToken->jti,
            'abilities'                         => $storedToken->abilities,
            'iat'                               => $storedToken->created_at->timestamp,
        ]) );

        if (method_exists($this, 'additionalJwtPayload')) {
            $payload->add($this->additionalJwtPayload());
        }

        return $payload;
    }
}
