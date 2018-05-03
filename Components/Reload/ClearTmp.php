<?php

namespace Kernel\Components\Reload;

class ClearTmp
{
    public function __construct()
    {
        secho("SYS", "已开启定时清理功能");
        $handle= getInstance();
        setTimezone();
        //添加定时器
        $dir = $handle->config->get('server.set.upload_tmp_dir', false);
        if ($dir) {
            swoole_timer_tick(3600 * 1000, function () use ($dir) {
                $time = time();
                foreach (glob($dir .DS.'*.tmp') as $file) {
                    if ($time - filemtime($file) > 3600) {
                        @unlink($file);
                    }
                }
            });
        }
    }
}
