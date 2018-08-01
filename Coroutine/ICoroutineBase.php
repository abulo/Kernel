<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-9-1
 * Time: 下午4:20
 */

namespace Kernel\Coroutine;

interface ICoroutineBase
{
    function send($callback);

    function destroy();
}
