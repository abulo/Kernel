<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-3-13
 * Time: 上午11:16
 */

namespace Kernel\Coroutine;

class CoroutineChangeToken
{
    public $token;
    public function __construct($token)
    {
        $this->token = $token;
    }
}
