<?php

/**
 * @category Cookie
 * @package Cookie
 * @link @Cookie
 * @author abulo.hoo
 */
namespace Kernel\Components\Session;

use Kernel\Utilities\Arr;

/**
 * Class Session
 *
 * @package FastD\Session
 */
class Session
{
    public $sessionId;
    public $sessionHandler;
    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
        $this->sessionHandler = getInstance()->getRedisProxy('session');
    }

    /**
     * 获取 session
     * @param   $key
     * @return
     */
    public function get($key)
    {
        $value = yield  $this->sessionHandler->get($this->sessionId);
		$value = json_decode($value,true);
        return $value[$key] ?? null;
    }

    public function getAll()
    {
        $value = yield  $this->sessionHandler->get($this->sessionId);
		$value = json_decode($value,true);
        return $value;
    }

    public function del($key)
    {
        $value = yield  $this->sessionHandler->get($this->sessionId);

        unset($value[$key]);
        $result = yield  $this->sessionHandler->set($this->sessionId, $value, 1800);
        return $result;
    }


    public function set($key, $val = null)
    {
        $session = yield  $this->sessionHandler->get($this->sessionId);
        if (!$session) {
            $session = [];
        }
        $data = [];
        if (is_array($key)) {
            $data = $key;
        } else {
            $data = [
                $key => $val
            ];
        }
        $newSession = Arr::merge($session, $data);
		$newSession = json_encode($newSession);
        $result = yield  $this->sessionHandler->set($this->sessionId, $newSession, 1800);
        return $result;
    }
}
