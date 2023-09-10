# Another laravel jwt auth

![Packagist License](https://img.shields.io/packagist/l/think.studio/laravel-jwt-auth?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/think.studio/laravel-jwt-auth)](https://packagist.org/packages/think.studio/laravel-jwt-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/think.studio/laravel-jwt-auth)](https://packagist.org/packages/think.studio/laravel-jwt-auth)
[![Build Status](https://scrutinizer-ci.com/g/dev-think-one/laravel-jwt-auth/badges/build.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-jwt-auth/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/dev-think-one/laravel-jwt-auth/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-jwt-auth/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dev-think-one/laravel-jwt-auth/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-jwt-auth/?branch=main)

Another laravel jwt auth package. \
This package has very slow support, you might be better off switching to an older and more used
package: [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth)

## Installation

Install the package via composer:

```shell
composer require think.studio/laravel-jwt-auth
```

You can publish the config file with:

```shell
php artisan vendor:publish --provider="JWTAuth\ServiceProvider" --tag="config"
```

You can publish migrations:

```shell
php artisan vendor:publish --provider="JWTAuth\ServiceProvider" --tag="migrations"
```

If you don't have encryption / decryption keys, generate them using the command

```shell
php artisan jwt:keys:generate
```

## Configuration

Update auth configuration

```php
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

```php
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

```php
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

```php
if(Auth::guard('my_api_guard_name')->check()) {
    Auth::guard('my_api_guard_name')->logout();
}
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
