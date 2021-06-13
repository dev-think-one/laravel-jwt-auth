# Another laravel jwt auth

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

If you do not have encrypt/decrypt keys than generate it with command

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
