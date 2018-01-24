<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-12-30
 * Time: 下午4:37
 */

namespace Kernel\Test;


use Kernel\Coroutine\CoroutineBase;

class TestHttpCoroutine extends CoroutineBase
{
    /**
     * @var TestRequest
     */
    public $testRequest;
    public $testResponse;

    public function __construct(TestRequest $testRequest)
    {
        parent::__construct();
        $this->testRequest = $testRequest;
        $this->request = '#TestRequest:' . $testRequest->server['path_info'];
        $this->testResponse = new TestResponse();
        getInstance()->onSwooleRequest($this->testRequest, $this->testResponse);
    }

    public function init()
    {
        $this->getCount = getTickTime();
        return $this;
    }

    public function send($callback)
    {

    }

    public function getResult()
    {
        parent::getResult();
        $result = $this->testResponse->getResult();
        return $result;
    }
}
