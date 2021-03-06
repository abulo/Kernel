<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-11-2
 * Time: 下午3:25
 */

namespace Kernel\Components\TimerTask;

use Kernel\Components\Event\Event;
use Kernel\Components\Event\EventDispatcher;
use Kernel\CoreBase\Child;
use Kernel\Memory\Pool;

/**
 * Class Timer
 * @package Kernel\Components\TimerTask
 */
class Timer
{
    protected static $instance;
    protected static $table;
    protected $flag = "TimerClear";

    public static function init()
    {
        self::$table = new \swoole_table(65536);
        self::$table->column('wid', \swoole_table::TYPE_INT, 4);
        self::$table->column('tid', \swoole_table::TYPE_INT, 4);
        self::$table->create();
    }

    /**
     * @return Timer
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Timer();
        }
        return self::$instance;
    }

    /**
     * Timer constructor.
     */
    public function __construct()
    {
        EventDispatcher::getInstance()->add($this->flag, function (Event $object) {
            $data = self::$table->get($object->data);
            if ($data['wid'] == getInstance()->getWorkerId()) {
                self::$table->del($object->data);
                \swoole_timer_clear($data['tid']);
            }
        });
    }

    /**
     * @param $name
     * @param int $ms
     * @param callable $callback
     * @throws \Exception
     */
    public function addTick($name, int $ms, callable $callback)
    {
        if (self::$table->exist($name)) {
            throw new \Exception("存在相同名字的定时器");
        }
        $tid = \swoole_timer_tick($ms, function () use ($callback) {
            $child = Pool::getInstance()->get(Child::class);
            sd_call_user_func($callback, $child);
            $child->destroy();
            Pool::getInstance()->push($child);
        });
        self::$table->set($name, ["wid" => getInstance()->getWorkerId(), "tid" => $tid]);
    }

    /**
     * @param $name
     */
    public function clearTick($name)
    {
        if (self::$table->exist($name)) {
            EventDispatcher::getInstance()->dispatch($this->flag, $name, false, true);
        }
    }
}
