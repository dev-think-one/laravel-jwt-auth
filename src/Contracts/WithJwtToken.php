<?php

namespace JWTAuth\Contracts;

use JWTAuth\JWTManager;
use JWTAuth\JWTPayload;

interface WithJwtToken
{

    /**
     * Payload key name.
     *
     * @return mixed
     */
    public function getJwtPayloadIdentifierKey();

    /**
     * Model token key name.
     *
     * @return mixed
     */
    public function getJwtAuthIdentifierKey();

    /**
     * Get stored JWT tokens for current model.
     *
     * @return mixed
     */
    public function storedJwtTokens();

    /**
     * Check abilities for current JWT token.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function jwtTokenCan(string $ability): bool;

    /**
     * Get loaded JWT token.
     *
     * @return JWTManager|null
     */
    public function currentJwtToken(): ?JWTManager;

    /**
     * Set JWT token.
     *
     * @param JWTManager $jwt
     *
     * @return self
     */
    public function withJwtToken(JWTManager $jwt): self;

    /**
     * Generate payload.
     *
     * @param string $name
     * @param array|string[] $abilities
     *
     * @return JWTPayload
     */
    public function createPayload(string $name, array $abilities = [ '*' ]): JWTPayload;
}