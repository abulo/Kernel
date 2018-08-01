<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-9-28
 * Time: 上午11:37
 */

namespace Kernel\Components\Middleware;

interface IMiddleware
{
    function setContext(&$context);

    function before_handle();

    function after_handle($path);
}
