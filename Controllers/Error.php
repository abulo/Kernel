<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 18-3-12
 * Time: 下午3:05
 */

namespace Kernel\Controllers;

use Kernel\CoreBase\ChildProxy;
use Kernel\CoreBase\Controller;

class Error extends Controller
{
    private $redis_prefix;

    public function __construct($proxy = ChildProxy::class)
    {
        parent::__construct($proxy);
        $this->redis_prefix = $this->config->get('error.redis_prefix');
    }

    public function defaultMethod()
    {
        $id = $this->http_input->get("id");
        $result = $this->redis->get($this->redis_prefix . $id);
        $this->http_output->end($result);
    }
}
