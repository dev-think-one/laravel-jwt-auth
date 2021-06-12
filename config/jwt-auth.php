<?php

return [

    'tables' => [
        'tokens' => 'api_jwt_tokens',
    ],

    'models' => [
        'tokens' => \JWTAuth\Eloquent\StoredJwtToken::class,
    ],

    'token' => [
        'expiration' => 3600 * 24 * 2, // seconds
        'jti'        => [
            'hash_algo' => 'sha256',
        ],
    ],

    'blocklist' => [
        'providers' => [
            'filesystem' => [
                'driver'  => \JWTAuth\BlockList\FileJwtBlockList::class,
                'options' => [
                    'disk'                            => 'local',
                    'directory'                       => 'jwt-black-list',
                    'minutes_to_obsolescence'         => 60 * 24 * 60, // 60 days
                    'remove_obsoleted_each_x_seconds' => 60 * 60, // 1 hour
                ],
            ],
        ],
    ],

];
