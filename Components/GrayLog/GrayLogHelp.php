<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-7-24
 * Time: 下午2:40
 */

namespace Kernel\Components\GrayLog;


class GrayLogHelp
{
    public static function init()
    {
        //开启一个UDP用于发graylog
        if (getInstance()->config->get('log.active') == 'graylog') {
            $socket = getInstance()->portManager->getFirstTypePort()['socket_name'];
            $udp_port = getInstance()->server->listen($socket, getInstance()->config['log']['graylog']['udp_send_port'], SWOOLE_SOCK_UDP);
            $udp_port->on('packet', function () {
            });
        }
    }

}
