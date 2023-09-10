<?php

namespace JWTAuth\Contracts;

use JWTAuth\Exceptions\JWTAuthException;

interface JWTManagerContract
{

    /**
     * Extract  JWT data from token string.
     *
     * @param string $token
     *
     * @return JWTManagerContract
     */
    public function decode(string $token): JWTManagerContract;

    /**
     * Create token string form internal manager data fields.
     *
     * @return string
     */
    public function encode(): string;

    /**
     * Set payload data.
     *
     * @param array|JWTPayloadContract $payload
     *
     * @return JWTManagerContract
     * @throws JWTAuthException
     */
    public function setPayload(array|JWTPayloadContract $payload): JWTManagerContract;

    /**
     * Get payload data.
     *
     * @return JWTPayloadContract
     */
    public function payload(): JWTPayloadContract;


    /**
     * Get previously created or decoded token string.
     *
     * @return string
     */
    public function getToken(): string;
}
