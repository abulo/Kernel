<?php

namespace Kernel\Components\WeChat\OfficialAccount\OAuth;

interface AccessTokenInterface
{
    /**
     * Return the access token string.
     *
     * @return string
     */
    public function getToken();
}
