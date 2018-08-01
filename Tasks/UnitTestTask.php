<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-1-4
 * Time: 上午10:46
 */

namespace Kernel\Tasks;

use Kernel\CoreBase\Task;
use Kernel\Test\TestModule;

class UnitTestTask extends Task
{
    public function startTest($dir)
    {
        new TestModule($dir);
    }
}
