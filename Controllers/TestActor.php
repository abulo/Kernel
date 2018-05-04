<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 18-3-28
 * Time: 上午10:23
 */

namespace Kernel\Controllers;

use Kernel\CoreBase\Actor;

class TestActor extends Actor
{

    /**
     * 处理注册状态
     * @param $key
     * @param $value
     */
    public function registStatusHandle($key, $value)
    {
        if ($key == "test") {
            $this->tick(1000, function () {
                throw new \Exception(1);
            });
        }
    }
    public function test()
    {
        $this->setStatus("test", 1);
    }
}
