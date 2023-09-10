<?php


namespace JWTAuth\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Used to cache data and debug after
 *
 * Class StoredJwtToken
 * @package JWTAuth\Eloquent

 */
class StoredJwtToken extends Model
{
    /**
     * @inheritdoc
     */
    protected $guarded = [];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'abilities' => 'json',
    ];

    /**
     * @inheritDoc
     */
    public function getTable(): string
    {
        return config('jwt-auth.tables.tokens', parent::getTable());
    }

    /**
     * Get model used token
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function tokenable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('tokenable');
    }
}
