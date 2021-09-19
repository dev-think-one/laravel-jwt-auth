# Another laravel jwt auth
[![Packagist License](https://img.shields.io/packagist/l/yaroslawww/laravel-jwt-auth?color=%234dc71f)](https://github.com/yaroslawww/laravel-jwt-auth/blob/master/LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/yaroslawww/laravel-jwt-auth)](https://packagist.org/packages/yaroslawww/laravel-jwt-auth)
[![Build Status](https://scrutinizer-ci.com/g/yaroslawww/laravel-jwt-auth/badges/build.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-jwt-auth/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/yaroslawww/laravel-jwt-auth/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-jwt-auth/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yaroslawww/laravel-jwt-auth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/laravel-jwt-auth/?branch=master)

Another laravel jwt auth package. \
This package has very slow support, you might be better off switching to an older and more used
package: [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth)

## Installation

Install the package via composer:

```bash
composer require yaroslawww/laravel-jwt-auth
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="JWTAuth\ServiceProvider" --tag="config"
```

You can publish migrations:

```bash
php artisan vendor:publish --provider="JWTAuth\ServiceProvider" --tag="migrations"
```

If you don't have encryption / decryption keys, generate them using the command

```shell
php artisan jwt:keys:generate
```

## Configuration

Update auth configuration

```injectablephp
// config/auth.php
  'guards' => [
        // ...
        'my_api_guard_name' => [
            'driver'      => 'jwt',
            'provider'    => 'users',
            'public_key'  => env('JWT_PUBLIC_KEY', 'jwt-keys/jwtRS256.key.pub'),
            'private_key' => env('JWT_PUBLIC_KEY', 'jwt-keys/jwtRS256.key'),
            'blocklist'   => 'filesystem',
            'options'     => [],
        ],
    ],
```

Update User model

```injectablephp
use Illuminate\Foundation\Auth\User as Authenticatable;
use JWTAuth\Contracts\WithJwtToken;

class User extends Authenticatable implements WithJwtToken
{
    use \JWTAuth\Eloquent\HasJwtToken;
    //...
}
```

## Usage

Login

```injectablephp
/** @var \JWTAuth\JWTGuard $auth */
$auth = Auth::guard('my_api_guard_name');
$token = $auth->attempt($request->only( 'email', 'password'));
if ($token) {
    $user = $auth->user();
    echo "Access token: {$token}";
    echo "User id: {$user->id}";
}
```

Logout

```injectablephp
if(Auth::guard('my_api_guard_name')->check()) {
    Auth::guard('my_api_guard_name')->logout();
}
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
