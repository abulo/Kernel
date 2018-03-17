<?php

/**
 * @category Cookie
 * @package Cookie
 * @link @Cookie
 * @author abulo.hoo
 */
namespace Kernel\Components\Session;


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
		$value = json_decode($value,true);

        unset($value[$key]);
		$value = json_encode($value);
        $result = yield  $this->sessionHandler->set($this->sessionId, $value, 1800);
        return $result;
    }


    public function set($key, $val = null)
    {
        $session = yield  $this->sessionHandler->get($this->sessionId);
		$session = json_decode($session,true);
        if (!$session) {
            $session = [];
        }
        $session[$key] = $val;
		$session = json_encode($session);
        $result = yield  $this->sessionHandler->set($this->sessionId, $session, 1800);
        return $result;
    }
}
