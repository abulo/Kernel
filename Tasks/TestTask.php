<?php
namespace Kernel\Tasks;

use Kernel\CoreBase\Task;

/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:06
 */
class TestTask extends Task
{
    public function testTimer()
    {
        print_r("test timer task\n");
    }

    public function testsend()
    {
        getInstance()->sendToAll(1);
    }

    public function test()
    {
        print_r(date('y-m-d H:i:s', time()) . "\n");
        //return 123;
    }

    public function contextTest()
    {
        //print_r($this->getContext());
    }

    public function test_task()
    {
        $testModel = $this->loader->model('TestModel', $this);
        $result = $testModel->test_task();
        print_r($result);
    }

    public function testPdo()
    {
        $testModel = $this->loader->model('TestModel', $this);
        $testModel->test_pdo();
    }

    public function testLong()
    {
        var_dump(time());
        sleep(4);
        var_dump(time());
        return 1;
    }

    /**
     * 测试停止
     */
    public function testStop()
    {
        while (true) {
            sleep(1);
            var_dump(1);
        }
    }

    public function testRedis()
    {
        $this->helpRedis();
        $this->helpRedis();
        $this->helpRedis();
        return 1;
    }

    public function helpRedis()
    {
        $result = $this->redis_pool->getCoroutine()->get('test');
        $result = $this->redis_pool->getCoroutine()->get('test2');
        $result = $this->mysql_pool->dbQueryBuilder->select('*')->from('task')
            ->whereIn('type', [0, 1])->where('status', 1)->coroutineSend();
        $this->testMysql();
        return 1;
    }

    public function testMysql()
    {
        $this->mysql_pool->dbQueryBuilder->coroutineSend(
            null,
            "update account set phone = 1 WHERE uid = 1"
        );
        throw new \Exception("aaaaaaaa");
    }
}
