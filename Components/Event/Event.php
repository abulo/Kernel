<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-8-4
 * Time: 上午11:23
 */

namespace Kernel\Components\Event;

class Event
{
    public $type;

    public $data;

    /**
     *
     * @param string $type
     * @param * $data
     * @return $this
     */
    public function reset($type, $data = null)
    {
        $this->type = $type;
        $this->data = $data;
        return $this;
    }
}
