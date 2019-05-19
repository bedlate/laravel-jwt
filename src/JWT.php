<?php

namespace bedlate\JWT;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

/**
 * Class JWT
 * @property \Lcobucci\JWT\Token $token;
 * @package bedlate\JWT
 */
class JWT
{
    const IDENTIFIER = 'uid';

    protected $token;

    /**
     * create a JWT token object
     * @param $identifier
     * @return $this
     */
    public function encode($identifier)
    {
        $signer = new Sha256();
        $exp = time() + config('jwt.expire');
        $this->token = (new Builder())
            ->setExpiration($exp)
            ->set(self::IDENTIFIER, $identifier)
            ->sign($signer, $this->getKey($identifier, $exp))
            ->getToken();
        return $this;
    }

    /**
     * parse a jwt token string to object
     * @param $token
     * @return $this
     */
    public function decode($token)
    {
        $this->token = (new Parser())->parse($token);
        return $this;
    }

    /**
     * verify sign
     * @return bool
     */
    public function verify()
    {
        if (!$this->validate()) {
            return false;
        }
        $signer = new Sha256();
        return $this->token->verify($signer, $this->getKey($this->getIdentifier(), $this->getExpire()));
    }

    /**
     * validate data
     * @return bool
     */
    public function validate()
    {
        $data = new ValidationData();
        return $this->token->validate($data);
    }

    /**
     * response to api
     * @return array
     */
    public function response()
    {
        $exp = $this->getExpire();
        return [
            'token' => (string)($this->token),
            'expire' => $exp,
        ];
    }

    /**
     * get JWT token
     * @return \Lcobucci\JWT\Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * get identifier
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->token->getClaim(self::IDENTIFIER);
    }

    /**
     * get expire time
     * @return mixed
     */
    protected function getExpire()
    {
        return $this->token->getClaim('exp');
    }

    /**
     * get real sign key
     * @param $identifier
     * @param $exp
     * @return string
     */
    private function getKey($identifier, $exp)
    {
        return 'key=' . config('jwt.key') . '&identifier=' . $identifier . '&exp=' . $exp;
    }

    public function fromUser($user)
    {
        $identifier = $user->getAuthIdentifier();
        return $this->encode($identifier)->response();
    }
}
