<?php

namespace Kernel\Components\WeChat\OfficialAccount\OAuth;

/**
 * Class AccessToken
 *
 * @package \Kernel\Components\WeChat\OfficialAccount\Kernel
 */
class AccessToken implements AccessTokenInterface, \ArrayAccess, \JsonSerializable
{
    use HasAttributes;

    public function __construct(array $attributes)
    {
        if (empty($attributes['access_token'])) {
            throw new \Exception('The key "access_token" could not be empty.');
        }

        $this->attributes = $attributes;
    }

    public function getToken()
    {
        return $this->getAttribute('access_token');
    }

    public function __toString()
    {
        return strval($this->getAttribute('access_token', ''));
    }

    public function jsonSerialize()
    {
        return $this->getToken();
    }
}
