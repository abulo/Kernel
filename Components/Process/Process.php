<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-8-15
 * Time: 上午9:41
 */

namespace Kernel\Components\Process;

use Kernel\Components\Event\EventDispatcher;
use Kernel\Start;
use Kernel\SwooleMarco;

abstract class Process extends ProcessRPC
{
    public $process;
    public $worker_id;
    protected $config;
    protected $log;
    protected $token = 0;
    protected $params;
    protected $socketBuff = "";

    /**
     * Process constructor.
     * @param string $name
     * @param $worker_id
     * @param $params
     */
    public function __construct($name, $worker_id, $params)
    {
        parent::__construct();
        $this->name = $name;
        $this->worker_id = $worker_id;
        getInstance()->workerId = $worker_id;
        $this->config = getInstance()->config;
        $this->log = getInstance()->log;
        $this->params = $params;
        if (getInstance()->server != null) {
            $this->process = new \swoole_process([$this, '__start'], false, 1);
            getInstance()->server->addProcess($this->process);
        }
    }

    public function __start($process)
    {
        \swoole_process::signal(SIGTERM, [$this, "__shutDown"]);
        getInstance()->workerId = $this->worker_id;
        if (!isDarwin()) {
            $process->name($this->name);
        }
        swoole_event_add($process->pipe, [$this, 'onRead']);
        getInstance()->server->worker_id = $this->worker_id;
        getInstance()->server->taskworker = false;
        go(function () use ($process) {
            $this->start($process);
        });
        //Code coverage
        register_tick_function([$this, 'onPhpTick']);
    }

    /**
     * Code coverage onPhpTick
     */
    public function onPhpTick()
    {
        if (!Start::getCoverage()) {
            return;
        }
        $redis_pool = getInstance()->getAsynPool("redisPool");
        if ($redis_pool != null) {
            $dump = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $file = explode("app-debug", $dump[0]['file'])[1]??null;
            if (!empty($file)) {
                $redis_pool->getSync()->zIncrBy(SwooleMarco::CodeCoverage, 1, $file . ":" . $dump[0]['line']);
            }
        }
    }

    /**
     * @param $process
     */
    abstract public function start($process);

    /**
     * 关服处理
     */
    public function __shutDown()
    {
        $this->onShutDown();
        secho("Process:$this->worker_id", get_class($this) . "关闭成功");
        exit();
    }

    abstract protected function onShutDown();

    /**
     * onRead
     */
    public function onRead()
    {
        while (true) {
            try {
                $recv = $this->process->read();
            } catch (\Throwable $e) {
                return;
            }
            $this->socketBuff .= $recv;
            while (strlen($this->socketBuff) > 4) {
                $len = unpack("N", $this->socketBuff)[1];
                if (strlen($this->socketBuff) >= $len) {//满足完整一个包
                    $data = substr($this->socketBuff, 4, $len-4);
                    $recv_data = \swoole_serialize::unpack($data);
                    $this->readData($recv_data);
                    $this->socketBuff = substr($this->socketBuff, $len);
                } else {
                    break;
                }
            }
        }
    }

    /**
     * @param $data
     */
    public function readData($data)
    {
        go(function () use ($data) {
            $message = $data['message'];
            switch ($data['type']) {
                case SwooleMarco::PROCESS_RPC:
                    $this->processPpcRun($message);
                    break;
                case SwooleMarco::PROCESS_RPC_RESULT:
                    EventDispatcher::getInstance()->dispatch($message['token'], $message['result'], true);
                    break;
                default:
                    if (!empty($data['func'])) {
                        $data['func']($message);
                    }
            }
        });
    }

    /**
     * 执行外部命令
     * @param $path
     * @param $params
     */
    protected function exec($path, $params)
    {
        $this->process->exec($path, $params);
    }
}
