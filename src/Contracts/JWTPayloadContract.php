<?php

namespace JWTAuth\Contracts;

interface JWTPayloadContract extends \JsonSerializable
{

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return JWTPayloadContract
     */
    public function add($key, $value = null): JWTPayloadContract;

    /**
     * Abilities list
     *
     * @return array|string[]
     */
    public function abilities(): array;

    /**
     * Check is payload is valid (token is valid)
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Check is expiration date passed
     *
     * @return bool
     */
    public function isPast(): bool;

    /**
     * Get expiration timestamp
     *
     * @return int
     */
    public function exp(): int;

    /**
     * Check is token payload has ability
     *
     * @param string $ability
     *
     * @return bool
     */
    public function can(string $ability): bool;

    /**
     * Check is token payload has not ability
     *
     * @param string $ability
     *
     * @return bool
     */
    public function cant(string $ability): bool;

    /**
     * Convert payload to array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * @param int $encodeOptions
     *
     * @return string
     */
    public function toJson(int $encodeOptions = 0): string;

    /**
     * Get key from payload
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;
}
