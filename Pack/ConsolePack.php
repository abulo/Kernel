<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-7-15
 * Time: 下午2:43
 */

namespace Kernel\Pack;

class ConsolePack extends NonJsonPack
{
    public function pack($data, $topic = null)
    {
        return json_encode(["topic" => $topic, "playlod" => $data], JSON_UNESCAPED_UNICODE);
    }
}
