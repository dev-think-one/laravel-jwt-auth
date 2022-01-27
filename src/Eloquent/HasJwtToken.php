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
     * @return string
     */
    public function getJwtPayloadIdentifierKey(): string
    {
        return $this->getJwtAuthIdentifierKey();
    }

    /**
     * Model token key name.
     *
     * @return string
     */
    public function getJwtAuthIdentifierKey(): string
    {
        if (method_exists($this, 'getAuthIdentifierName')) {
            return $this->getAuthIdentifierName();
        }

        return 'id';
    }

    /**
     * Get stored JWT tokens for current model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     * @throws \Exception
     */
    public function storedJwtTokens(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        if (!method_exists($this, 'morphMany')) {
            throw new \Exception('Method "morphMany" should be provided.');
        }

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
     * @return static
     */
    public function withJwtToken(JWTManager $jwt): static
    {
        $this->jwtToken = $jwt;

        return $this;
    }

    /**
     * Generate payload.
     *
     * @param  string  $name
     * @param  array|string[]  $abilities
     *
     * @return JWTPayload
     * @throws \Exception
     */
    public function createPayload(string $name, array $abilities = [ '*' ]): JWTPayload
    {
        $storedToken = $this->storedJwtTokens()->create([
            'name'      => $name,
            'jti'       => hash(config('jwt-auth.token.jti.hash_algo', 'sha256'), Str::random(40)),
            'abilities' => $abilities,
            'exp'       => Carbon::now()->addSeconds($this->jwtTokenLifetimeInSeconds())->timestamp,
        ]);
        $payload     = ( new JWTPayload([
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

    public function jwtTokenLifetimeInSeconds():int
    {
        return (int) config('jwt-auth.token.expiration', 3600 * 24);
    }
}
