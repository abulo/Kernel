<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-7-25
 * Time: 上午11:09
 */

namespace Kernel\Asyn;

interface IAsynPool
{
    function getAsynName();

    function pushToPool($client);

    function getSync();

    function setName($name);
}
