<?php


namespace JWTAuth;

use \JWTAuth\Contracts\JWTPayloadContract;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class JWTPayload implements JWTPayloadContract
{

    /**
     * JWT payload data
     *
     * @var array
     */
    protected array $payload = [];

    /**
     * JWTPayload constructor.
     *
     * @param array $payload
     */
    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    /**
     * @inheritDoc
     */
    public function add($key, $value = null): JWTPayloadContract
    {
        if (is_array($key) && count(func_get_args()) == 1) {
            $this->payload = array_merge($this->payload, $key);
        } else {
            $this->payload[ $key ] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function abilities(): array
    {
        $a = $this->payload['abilities'] ?? [];

        return is_array($a) ? $a : [];
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return !$this->isPast();
    }

    /**
     * @inheritDoc
     */
    public function isPast(): bool
    {
        return Carbon::createFromTimestampUTC($this->exp())->timezone('UTC')->isPast();
    }

    /**
     * @inheritDoc
     */
    public function exp(): int
    {
        $exp = $this->payload['exp'] ?? 0;

        return (int) (is_numeric($exp) ? $exp : 0);
    }

    /**
     * @inheritDoc
     */
    public function can(string $ability): bool
    {
        return in_array('*', $this->abilities()) ||
               array_key_exists($ability, array_flip($this->abilities()));
    }

    /**
     * @inheritDoc
     */
    public function cant(string $ability): bool
    {
        return !$this->can($ability);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->payload;
    }

    /**
     * @inheritDoc
     */
    public function toJson(int $encodeOptions = 0): string
    {
        return json_encode($this->toArray(), $encodeOptions) ?: '';
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->payload, $key, $default);
    }
}
