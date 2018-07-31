<?php
/**
 * Created by PhpStorm.
 * User: abulo
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

    public function init(TestRequest $testRequest)
    {
        $this->testRequest = $testRequest;
        $this->request = '#TestRequest:' . $testRequest->server['path_info'];
        $this->testResponse = new TestResponse();
        getInstance()->onSwooleRequest($this->testRequest, $this->testResponse);
        return $this->testResponse;
    }

    public function send($callback)
    {
    }
}
