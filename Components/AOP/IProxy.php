<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-10-16
 * Time: 上午11:47
 */

namespace Kernel\Components\AOP;

interface IProxy
{
    function beforeCall($name, $arguments);

    function afterCall($name, $arguments);
}
