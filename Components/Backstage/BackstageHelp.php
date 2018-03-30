<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-11-7
 * Time: 下午3:18
 */

namespace Kernel\Components\Backstage;

use Kernel\CoreBase\PortManager;

class BackstageHelp
{
    public static $set = false;

    public static function init()
    {
        if (self::$set) {
            return;
        }
        if (!getInstance()->config->get('backstage.enable', false)) {
            return;
        }
        $name = getInstance()->config->get('backstage.socket');
        $port = getInstance()->config->get('backstage.websocket_port');
        $ports = getInstance()->config["ports"];
        $ports[] = [
            'socket_type' => PortManager::SOCK_WS,
            'socket_name' => $name,
            'socket_port' => $port,
            'route_tool' => 'ConsoleRoute',
            'pack_tool' => 'ConsolePack',
            'opcode' => PortManager::WEBSOCKET_OPCODE_TEXT,
            'event_controller_name' => Console::class,
            'connect_method_name' => "onConnect",
            'close_method_name' => "onClose",
            'method_prefix' => 'back_',
            'weight'=>100,
            'middlewares' => ['MonitorMiddleware', 'NormalHttpMiddleware']
        ];
        getInstance()->config->set("ports", $ports);
        $timerTask = getInstance()->config["timerTask"];
        $timerTask[] = [
            'model_name' => ConsoleModel::class,
            'method_name' => 'getNodeStatus',
            'interval_time' => '1',
        ];
        getInstance()->config->set("timerTask", $timerTask);
        self::$set = true;
    }
}
