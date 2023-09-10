<?php


namespace JWTAuth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Log;
use JWTAuth\Contracts\JwtBlockListContract;
use JWTAuth\Contracts\WithJwtToken;
use JWTAuth\Exceptions\JWTAuthException;

/**
 * Class JwtGuard
 * @package JWTAuth
 *
 * @property EloquentUserProvider $provider
 */
class JWTGuard implements Guard
{
    use GuardHelpers;

    /**
     * JWT token manager.
     *
     * @var JWTManager
     */
    protected JWTManager $jwt;

    /**
     * JWT black list manager.
     *
     * @var JwtBlockListContract
     */
    protected JwtBlockListContract $blockList;

    /**
     * The name of the query string item from the request containing the API token.
     *
     * @var string
     */
    protected string $inputKey;

    public function __construct(UserProvider $provider, JWTManager $jwt, JwtBlockListContract $blockList, array $options = [])
    {
        $this->jwt       = $jwt;
        $this->blockList = $blockList;
        $this->provider  = $provider;
        $this->inputKey  = $options['input_key'] ?? 'api_token';
    }

    /**
     * @return JwtBlockListContract
     */
    public function blockList(): JwtBlockListContract
    {
        return $this->blockList;
    }


    /**
     * @inheritDoc
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $jwtToken = $this->decodeToken($this->getTokenForRequest());

        if (
            $jwtToken &&
            $identifier = $jwtToken->payload()->get($this->provider->createModel()->getJwtPayloadIdentifierKey(), false)
        ) {
            /** @var WithJwtToken $user */
            $user = $this->provider->retrieveByCredentials([
                $this->provider->createModel()->getJwtAuthIdentifierKey() => $identifier,
            ]);
            if ($user && $user instanceof WithJwtToken) {
                $user->withJwtToken($jwtToken);
            }
        }

        return $this->user = $user;
    }

    public function validate(array $credentials = [])
    {
        if (empty($credentials)) {
            return false;
        }

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        $request = request();

        $token = $request->query($this->inputKey);

        if (empty($token)) {
            $token = $request->input($this->inputKey);
        }

        if (empty($token)) {
            $token = $request->bearerToken();
        }

        if (empty($token)) {
            $token = $request->getPassword();
        }

        return $token;
    }

    /**
     * Attempt to authenticate the user and return the token.
     *
     * @param  array  $credentials
     *
     * @return false|string
     * @throws JWTAuthException
     */
    public function attempt(array $credentials)
    {

        /** @var WithJwtToken|Authenticatable $user */
        if (!($user = $this->provider->retrieveByCredentials($credentials))) {
            return false;
        }

        if (isset($credentials['password'])) {
            if (!$this->provider->validateCredentials($user, $credentials)) {
                return false;
            }
        }

        if (!($user instanceof WithJwtToken)) {
            throw new JWTAuthException('User should implement "WithJwtToken"');
        }

        try {
            $token = $this->createTokenForUser($user);
            $this->setUser($user);

            return $token->encode();
        } catch (\Exception $e) {
            throw new JWTAuthException('Token creation error', 500, $e);
        }
    }

    public function logout()
    {
        /** @var WithJwtToken $user */
        $user = $this->user();

        if ($user && $user->currentJwtToken()) {
            $this->blockList->add($user->currentJwtToken());
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->unsetUser();
    }

    public function unsetUser(): static
    {
        $this->user = null;

        return $this;
    }

    public function getJWTManager(): JWTManager
    {
        return $this->jwt;
    }

    public function decodeToken(?string $token): ?Contracts\JWTManagerContract
    {
        if ($token) {
            try {
                $jwtToken = $this->jwt->decode($token);
                if (
                    !$this->blockList->isBlockListed($jwtToken) &&
                    $jwtToken->payload()->isValid()
                ) {
                    return $jwtToken;
                }
            } catch (\Exception $e) {
                // Token not valid
                Log::info($e->getMessage());
            }
        }

        return null;
    }

    public function createTokenForUser(WithJwtToken $user, string $name = 'jwt', array $abilities = ['*'], ?int $lifetimeInSeconds = null): Contracts\JWTManagerContract
    {
        $token = $this->jwt->setPayload($user->createPayload($name, $abilities, $lifetimeInSeconds));
        $user->withJwtToken($token);

        return $token;
    }
}
