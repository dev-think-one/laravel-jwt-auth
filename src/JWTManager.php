<?php

namespace JWTAuth;

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JWTAuth\Contracts\JWTManagerContract;
use JWTAuth\Contracts\JWTPayloadContract;

/**
 * Class JWTManager
 * @package JWTAuth
 */
class JWTManager implements JWTManagerContract
{

    /**
     * Path to public key
     *
     * @var string
     */
    protected string $publicKey;

    /**
     * Path to private key
     *
     * @var string
     */
    protected string $privateKey;

    /**
     * Payload data
     *
     * @var JWTPayloadContract
     */
    protected JWTPayloadContract $payload;

    /**
     * String token
     *
     * @var string
     */
    protected string $token;


    public function __construct(string $publicKey = '', string $privateKey = '')
    {
        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;

        $this->payload = new JWTPayload();
    }

    /**
     * @inheritDoc
     */
    public function decode(string $token): JWTManagerContract
    {
        $publicKey = file_get_contents(storage_path($this->publicKey));

        $payloadArray = (array) JWT::decode($token, new Key($publicKey, 'RS256'));

        $this->payload = new JWTPayload($payloadArray);

        $this->token = $token;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function encode(): string
    {
        $privateKey = file_get_contents(storage_path($this->privateKey));

        $this->token = JWT::encode($this->payload->toArray(), $privateKey, 'RS256');

        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function setPayload(array|JWTPayloadContract $payload): JWTManagerContract
    {
        $this->payload = is_array($payload) ? new JWTPayload($payload) : $payload;

        $this->token = '';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function payload(): JWTPayloadContract
    {
        return $this->payload;
    }

    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        if (!$this->token) {
            $this->encode();
        }

        return $this->token;
    }

    public function toArray(): array
    {
        return $this->payload->toArray();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
