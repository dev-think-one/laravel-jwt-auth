<?php


namespace JWTAuth\Contracts;

use JWTAuth\JWTManager;

interface JwtBlockListContract
{
    /**
     * Add token to blocklist
     *
     * @param JWTManager $token
     *
     * @return $this
     */
    public function add(JWTManager $token): self;

    /**
     * Check is token in blocklist
     *
     * @param JWTManager $token
     *
     * @return bool
     */
    public function isBlockListed(JWTManager $token): bool;
}
