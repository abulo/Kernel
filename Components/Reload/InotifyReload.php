<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-9-19
 * Time: 上午9:17
 */

namespace Kernel\Components\Reload;

class InotifyReload
{
    public $swoole;
    public $inotify;
    public $reloadFileTypes = array('php','tpl');
    public $watchFiles = array();
    public $events;
    public function __construct()
    {
        $this->swoole = getInstance()->server;



        if (!extension_loaded('inotify')) {
            $this->unUseInotify();
        } else {
            secho("SYS", "已开启代码热重载");
            // 初始化inotify句柄
            $this->inotify = inotify_init();
            // 设置为非阻塞
            stream_set_blocking($this->inotify, 0);
            $this->events = IN_MODIFY | IN_DELETE | IN_CREATE | IN_MOVE | IN_IGNORED;

            $reloadPath = getInstance()->config->get('monitor');
            if ($reloadPath===false) {
                throw new \Exception("monitor null");
            } else {
                foreach ($reloadPath as $key => $path) {
                    $this->watch($path);
                }
            }
            $this->useInotify();
        }
    }

    /**
     * 使用inotify
     * @return
     */
    public function useInotify()
    {

        swoole_event_add($this->inotify, function ($inotify_fd) {
            $events = inotify_read($inotify_fd);
            if ($events) {
                foreach ($events as $ev) {
                    // 更新的文件
                    $file = $this->watchFiles[$ev['wd']];
                    secho("RELOAD", $file . " update");
                    unset($this->watchFiles[$ev['wd']]);
                    // 需要把文件重新加入监控
                    if (file_exists($file)) {
                        $wd = inotify_add_watch($inotify_fd, $file, $this->events);
                        $this->watchFiles[$wd] = $file;
                    }
                }
                $this->swoole->reload();
            }
        }, null, SWOOLE_EVENT_READ);
    }

    /**
     * 不使用inotify
     * @return
     */
    public function unUseInotify()
    {
        secho("RELOAD", "不支持");
    }


    /**
     * 需要监控的目录
     * @param   string $dir
     * @return
     */
    public function watch($dir)
    {
        //目录不存在
        if (!is_dir($dir)) {
            throw new \Exception("[$dir] is not a directory.");
        }

        // 递归遍历目录里面的文件
        $dir_iterator = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($dir_iterator);
        foreach ($iterator as $file) {
            // 只监控定义的文件格式
            if (in_array(pathinfo($file, PATHINFO_EXTENSION), $this->reloadFileTypes)) {
                $wd = inotify_add_watch($this->inotify, $file, $this->events);
                $this->watchFiles[$wd] = $file;
            }
        }

        return;
    }
}
