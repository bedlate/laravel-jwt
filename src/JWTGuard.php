<?php

namespace bedlate\JWT;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Traits\Macroable;

/**
 * Class JWTGuard
 * @property \bedlate\JWT\JWT $jwt;
 * @property \Illuminate\Support\Facades\Auth $auth;
 * @package bedlate\JWT
 */
class JWTGuard implements Guard
{
    use GuardHelpers, Macroable;

    protected $jwt, $provider, $request;

    protected $token, $lastAttempted;

    /**
     * Create a new authentication guard.
     * JWTGuard constructor.
     * @param JWT $jwt
     * @param Request $request
     * @param UserProvider $provider
     */
    public function __construct(JWT $jwt, Request $request, UserProvider $provider)
    {
        $this->jwt = $jwt;
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (null !== $this->user) {
            return $this->user;
        }
        $token = $this->getTokenStr();

        if ($token == null) {
            return null;
        }
        $jwt = $this->jwt->decode($token);
        if ($jwt->verify() && ($uid = $jwt->getIdentifier())) {
            return $this->user = $this->provider->retrieveById($uid);
        }
        return null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     * @param array $credentials
     * @return array|bool
     */
    public function attempt(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
        if ($this->hasValidCredentials($user, $credentials)) {
            return $this->login($user);
        }
        return false;
    }

    /**
     * Log a user into the application.
     * @param Authenticatable $user
     * @return array
     */
    public function login(Authenticatable $user)
    {
        $this->user = $user;
        $identifier = $user->getAuthIdentifier();
        return $this->jwt->encode($identifier)->response();
    }

    /**
     * Refresh token for current user
     * @return array
     */
    public function refresh()
    {
        return $this->login($this->user);
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Get token from query string or header
     * @return mixed
     */
    protected function getTokenStr()
    {
        $query = config('jwt.query');
        if ($this->request->has($query)) {
            $token = $this->request->get($query);
        } else {
            $token = $this->request->bearerToken();
        }
        return $token;
    }

    public function tokenById(int $id)
    {
        if ($this->user = $this->provider->retrieveById($id)) {
            return $this->jwt->fromUser($this->user);
        }
    }
}
