<?php

/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-7-25
 * Time: 上午10:29
 */

namespace Kernel;

use Kernel\CoreBase\PortManager;

class Start
{
    /**
     * Daemonize.
     *
     * @var bool
     */
    protected static $daemonize = false;

    /**
     * @var array
     */
    protected static $debug_filter;

    /**
     * @var
     */
    protected static $debug;
    /**
     * @var
     */
    protected static $xdebug;
    /**
     * @var
     */
    protected static $coverage;
    /**
     * @var string
     */
    protected static $startTime;

    /**
     * @var
     */
    protected static $startMillisecond;

    /**
     * @var
     */
    protected static $leader;

    /**
     * @var
     */
    protected static $xdebug_table;
    /**
     * 单元测试
     * @var bool
     */
    public static $testUnity = false;
    /**
     * 单元测试文件目录
     * @var string
     */
    public static $testUnityDir = '';

    /**
     * worker instance.
     *
     * @var SwooleServer
     */
    protected static $_worker = null;
    /**
     * Maximum length of the show names.
     *
     * @var int
     */
    protected static $_maxShowLength = 12;


    protected static $sh = "IyEvYmluL2Jhc2gKCiMgc2V0IHVsaW1pdApsaW1pdCgpCnsKZWNobyAidWxpbWl0IC1TSG4gMTAyNDAwIiA+Pi9ldGMvcmMubG9jYWwKY2F0ID4+IC9ldGMvc2VjdXJpdHkvbGltaXRzLmNvbmYgPDwgRU9GCiogICAgICAgICAgIHNvZnQgICBub2ZpbGUgICAgICAgNjU1MzUKKiAgICAgICAgICAgaGFyZCAgIG5vZmlsZSAgICAgICA2NTUzNQpFT0YKfQoKIyBzZXQgc3lzY3RsCnN5c2N0bCgpCnsKY3AgL2V0Yy9zeXNjdGwuY29uZiAvZXRjL3N5c2N0bC5jb25mLSQoZGF0ZSArJUYpLmJhawp0cnVlID4gL2V0Yy9zeXNjdGwuY29uZgpjYXQgPj4gL2V0Yy9zeXNjdGwuY29uZiA8PCBFT0YKbmV0LmlwdjQuaXBfZm9yd2FyZCA9IDAKbmV0LmlwdjQuY29uZi5kZWZhdWx0LnJwX2ZpbHRlciA9IDEKbmV0LmlwdjQuY29uZi5kZWZhdWx0LmFjY2VwdF9zb3VyY2Vfcm91dGUgPSAwCmtlcm5lbC5zeXNycSA9IDAKa2VybmVsLmNvcmVfdXNlc19waWQgPSAxCm5ldC5pcHY0LnRjcF9zeW5jb29raWVzID0gMQprZXJuZWwubXNnbW5iID0gNjU1MzYKa2VybmVsLm1zZ21heCA9IDY1NTM2Cmtlcm5lbC5zaG1tYXggPSA2ODcxOTQ3NjczNgprZXJuZWwuc2htYWxsID0gNDI5NDk2NzI5NgpuZXQuaXB2NC50Y3BfbWF4X3R3X2J1Y2tldHMgPSA2MDAwCm5ldC5pcHY0LnRjcF9zYWNrID0gMQpuZXQuaXB2NC50Y3Bfd2luZG93X3NjYWxpbmcgPSAxCm5ldC5pcHY0LnRjcF9ybWVtID0gNDA5NiA4NzM4MCA0MTk0MzA0Cm5ldC5pcHY0LnRjcF93bWVtID0gNDA5NiAxNjM4NCA0MTk0MzA0Cm5ldC5jb3JlLndtZW1fZGVmYXVsdCA9IDgzODg2MDgKbmV0LmNvcmUucm1lbV9kZWZhdWx0ID0gODM4ODYwOApuZXQuY29yZS5ybWVtX21heCA9IDE2Nzc3MjE2Cm5ldC5jb3JlLndtZW1fbWF4ID0gMTY3NzcyMTYKbmV0LmNvcmUubmV0ZGV2X21heF9iYWNrbG9nID0gMjYyMTQ0Cm5ldC5jb3JlLnNvbWF4Y29ubiA9IDI2MjE0NApuZXQuaXB2NC50Y3BfbWF4X29ycGhhbnMgPSAzMjc2ODAwCm5ldC5pcHY0LnRjcF9tYXhfc3luX2JhY2tsb2cgPSAyNjIxNDQKbmV0LmlwdjQudGNwX3RpbWVzdGFtcHMgPSAwCm5ldC5pcHY0LnRjcF9zeW5hY2tfcmV0cmllcyA9IDEKbmV0LmlwdjQudGNwX3N5bl9yZXRyaWVzID0gMQpuZXQuaXB2NC50Y3BfdHdfcmVjeWNsZSA9IDEKbmV0LmlwdjQudGNwX3R3X3JldXNlID0gMQpuZXQuaXB2NC50Y3BfbWVtID0gOTQ1MDAwMDAgOTE1MDAwMDAwIDkyNzAwMDAwMApuZXQuaXB2NC50Y3BfZmluX3RpbWVvdXQgPSAxCm5ldC5pcHY0LnRjcF9rZWVwYWxpdmVfdGltZSA9IDEyMDAKbmV0LmlwdjQuaXBfbG9jYWxfcG9ydF9yYW5nZSA9IDEwMjQgNjU1MzUKI25ldC5pcHY0LmljbXBfZWNob19pZ25vcmVfYWxsID0gMSAgI+emgXBpbmfvvIzlpoLmnpzmnIluYWdpb3Pnm5HmjqfvvIzov5nmraXlj6/nnIHljrsKRU9GCi9zYmluL3N5c2N0bCAtcAplY2hvICJzeXNjdGwgc2V0IE9LISEiCn0KCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpsaW1pdApzeXNjdGwK";

    /**
     * Run all worker instances.
     *
     * @return void
     * @throws \Exception
     */
    public static function run($daemonize = false)
    {
        self::$debug = new \swoole_atomic(0);
        self::$xdebug = false;
        self::$coverage = false;
        self::$leader = new \swoole_atomic(0);
        self::$xdebug_table = new \swoole_table(1);
        self::$xdebug_table->column('wid', \swoole_table::TYPE_INT, 8);
        self::$xdebug_table->column('cid', \swoole_table::TYPE_INT, 8);
        self::$xdebug_table->create();
        self::$startTime = date('Y-m-d H:i:s');
        self::$startMillisecond = getMillisecond();
        self::$daemonize = $daemonize;
        //如果是非daemonize模式,则开启debug观察模式
        if (!$daemonize) {
            self::setDebug(1);
        }
        //检查环境是否支持
        self::checkSapiEnv();
        //设置
        self::init();
        //解析命令
        self::parseCommand();

        self::initWorkers();
        self::startSwoole();
    }

    /**
     * Set process name.
     *
     * @param string $title
     * @return void
     */
    public static function setProcessTitle($title)
    {
        if (isDarwin()) {
            return $title;
        }
        // >=php 5.5
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } // Need proctitle when php<=5.5 .
        else {
            @swoole_set_process_name($title);
        }
        return $title;
    }

    public static function setDaemonize()
    {
        self::$daemonize = true;
    }

    public static function getDaemonize()
    {
        return self::$daemonize ? 1 : 0;
    }

    public static function getDebug()
    {
        return self::$debug->get() == 1 ? true : false;
    }

    public static function setDebug($debug)
    {
        self::$debug->set($debug ? 1 : 0);
        if ($debug) {
            secho("SYS", "DEBUG开启");
        } else {
            secho("SYS", "DEBUG关闭");
        }
    }
    public static function getCoverage()
    {
        return self::$coverage;
    }

    public static function setCoverage($coverage)
    {
        self::$coverage = $coverage;
    }
    public static function getXDebug()
    {
        return self::$xdebug;
    }

    public static function setXDebug($debug)
    {
        self::$xdebug = $debug;
    }
    public static function isLeader()
    {
        return self::$leader->get() == 1 ? true : false;
    }

    public static function setLeader($bool)
    {
        self::$leader->set($bool ? 1 : 0);
        if (getInstance()->isCluster()) {
            if ($bool) {
                secho("CONSUL", "Leader变更，被选举为Leader");
            } else {
                secho("CONSUL", "Leader变更，本机不是Leader");
            }
        }
    }

    public static function getDebugFilter()
    {
        return self::$debug_filter ?? [];
    }

    public static function getStartTime()
    {
        return self::$startTime;
    }

    public static function getStartMillisecond()
    {
        return self::$startMillisecond;
    }

    public static function getLockXDebug()
    {
        $result = self::$xdebug_table->get('debug');
        $wid = getInstance()->getWorkerId();
        $cid = \Swoole\Coroutine::getuid();
        if ($cid == -1) {
            return false;
        }
        if ($result === false) {
            self::$xdebug_table->set("debug", ['wid' => $wid, 'cid' => $cid]);
            return true;
        } else {
            if ($result['wid'] == $wid && $result['cid'] == $cid) {
                return true;
            } else {
                return false;
            }
        }
    }
    public static function cleanXDebugLock()
    {
        self::$xdebug_table->del('debug');
    }








    /**
     * Init All worker instances.
     *
     * @return void
     */
    protected static function initWorkers()
    {
        // Worker name.
        if (empty(self::$_worker->name)) {
            self::$_worker->name = 'none';
        }
        // Get unix user of the worker process.
        if (empty(self::$_worker->user)) {
            self::$_worker->user = self::getCurrentUser();
        } else {
            if (posix_getuid() !== 0 && self::$_worker->user != self::getCurrentUser()) {
                secho("STA", 'Warning: You must have the root privileges to change uid and gid.');
            }
        }
    }

    /**
     * Get unix user of current porcess.
     *
     * @return string
     */
    protected static function getCurrentUser()
    {
        $user_info = posix_getpwuid(posix_getuid());
        return $user_info['name'];
    }


    /**
     * Check sapi.
     *
     * @return void
     */
    protected static function checkSapiEnv()
    {
        // Only for cli.
        if (php_sapi_name() != "cli") {
            exit("only run in command line mode \n");
        }
    }

    /**
     * Init.
     *
     * @return void
     */
    protected static function init()
    {
        // Process title.
        self::setProcessTitle(getServerName());
    }



    /**
     * Fork some worker processes.
     *
     * @return void
     */
    protected static function startSwoole()
    {
        self::$_worker->start();
        self::displayUI();
        self::$_worker->monitor->outPutNowStatus();
    }



    public static function initServer($swooleServer)
    {
        self::$_worker = $swooleServer;
    }



    /**
     * Display staring UI.
     *
     * @return void
     */
    protected static function displayUI()
    {
        $config = self::$_worker->config;
        echo "\033[2J";
        echo "\033[1A\n\033[K------------------------\033[47;30m SWOOLE_DISTRIBUTED \033[0m---------------------------\n\033[0m";
        echo 'System:', PHP_OS, "\t\t\t";
        echo 'Framework version:', SwooleServer::version, "\n";
        echo 'Swoole version: ', SWOOLE_VERSION, "\t\t";
        echo 'PHP version: ', PHP_VERSION, "\n";
        echo 'worker_num: ', $config->get('server.set.worker_num', 0), "\t\t\t";
        echo 'task_num: ', $config->get('server.set.task_worker_num', 0), "\n";
        echo "------------------------------\033[47;30m" . self::$_worker->name . "\033[0m-----------------------------------\n";
        echo "\033[47;30mS_TYPE\033[0m", str_pad(
            '',
            self::$_maxShowLength - strlen('S_TYPE')
        ), "\033[47;30mS_STAT\033[0m", str_pad(
            '',
            self::$_maxShowLength - strlen('S_NAME')
        ), "\033[47;30mS_NAME\033[0m", str_pad(
            '',
            self::$_maxShowLength - strlen('S_NAME')
        ), "\033[47;30mS_PORT\033[0m", str_pad(
            '',
            self::$_maxShowLength - strlen('S_PORT')
        ), "\033[47;30mS_PACK\033[0m", str_pad(
            '',
            self::$_maxShowLength - strlen('S_')
        ), "\033[47;30m", "S_MIDD\033[0m\n";
        switch (self::$_worker->name) {
            case SwooleDistributedServer::SERVER_NAME:
                $ports = $config['ports'];
                foreach ($ports as $key => $value) {
                    $middleware = '';
                    foreach ($value['middlewares'] ?? [] as $m) {
                        $middleware .= '[' . $m . ']';
                    }

                    $active = exec("netstat -nlp|grep ':" . $value['socket_port'] . "'| grep -v 'grep' | awk '{print $4}'");
                    if ($active) {
                        $active = 'active';
                    } else {
                        $active = 'inactive';
                    }
                    echo str_pad(
                        PortManager::getTypeName($value['socket_type']),
                        self::$_maxShowLength
                    ), str_pad(
                        $active,
                        self::$_maxShowLength
                    ), str_pad(
                        $value['socket_name'],
                        self::$_maxShowLength
                    ), str_pad(
                        $value['socket_port'],
                        self::$_maxShowLength
                    ), str_pad(
                        $value['pack_tool'] ?? PortManager::getTypeName($value['socket_type']),
                        self::$_maxShowLength + 4
                    ), str_pad(
                        $middleware,
                        self::$_maxShowLength
                    );
                    echo "\n";
                }

                $active = exec("netstat -nlp|grep ':" . self::$_worker->config->get('cluster.port', '--') . "'| grep -v 'grep' | awk '{print $4}'");
                if ($active) {
                    $active = 'active';
                } else {
                    $active = 'inactive';
                }
                echo str_pad(
                    'CLUSTER',
                    self::$_maxShowLength
                ), str_pad(
                    $active,
                    self::$_maxShowLength
                ), str_pad(
                    '0.0.0.0',
                    self::$_maxShowLength
                ), str_pad(
                    self::$_worker->config->get('cluster.port', '--'),
                    self::$_maxShowLength - 2
                );
                if (self::$_worker->config->get('consul.enable', false)) {
                    echo " \033[32;40m [CLUSTERPACK] \033[0m\n";
                } else {
                    echo " \033[31;40m [CLOSE] \033[0m\n";
                }
                break;
        }
        echo "-----------------------------------------------\n";
    }



    /**
     * Parse command.
     * php yourfile.php start | stop | reload
     *
     * @return void
     */
    protected static function parseCommand()
    {
        global $argv;
        // Check argv;
        $start_file = $argv[0];
        if (!isset($argv[1])) {
            exit("Usage: php yourfile.php {start|stop|kill|reload|restart|status|optimiza|coverage}\n");
        }

        // Get command.
        $command = trim($argv[1]);
         //获取运行框架的用户
        $current_user = trim(shell_exec('whoami'));
        $sys_user = self::$_worker->config->get('server.set.user');

        if ($current_user != $sys_user && $command != 'optimiza') {
            secho("STA", "Warning: Please use " . $sys_user . " Run script");
            exit;
        }




        //主进程
        $master_pid = ServerPid::getMasterPid(self::$_worker->pidFilePath);
        //管理进程
        $manager_pid = ServerPid::getManagerPid(self::$_worker->pidFilePath);



        $server_name = getServerName();
        if (!$master_pid) {
            $master_pid = exec("ps -ef | grep $server_name-master | grep -v 'grep ' | awk '{print $2}'");
        }
        if (!$manager_pid) {
            $manager_pid = exec("ps -ef | grep $server_name-manager | grep -v 'grep ' | awk '{print $2}'");
        }



        $master_is_alive = $master_pid && @posix_kill($master_pid, 0);

        if ($master_is_alive) {
            if ($command === 'start') {
                secho("STA", "Swoole[$start_file] already running");
                exit;
            }
        } elseif ($command !== 'start') {
            secho("STA", "Swoole[$start_file] not run");
            exit;
        }

        // execute command.
        switch ($command) {
            case 'start':
                secho("STA", "Swoole[$start_file] start success");
                break;
            case 'kill':
                exec("ps -ef|grep $server_name|grep -v grep|cut -c 9-15|xargs kill -9");
                break;
            case 'stop':
                secho("STA", "Swoole[$start_file] is stoping ...");
                $master_pid && posix_kill($master_pid, SIGTERM);
                $timeout = 40;
                $start_time = time();
                while (1) {
                    $master_is_alive = $master_pid && posix_kill($master_pid, 0);
                    if ($master_is_alive) {
                        if (time() - $start_time >= $timeout) {
                            secho("STA", "Swoole[$start_file] stop fail");
                            exit;
                        }
                        usleep(10000);
                        continue;
                    }
                    secho("STA", "Swoole[$start_file] stop success");
                    break;
                }
                exit(0);
                break;
            case 'reload':
                posix_kill($manager_pid, SIGUSR1);
                secho("STA", "Swoole[$start_file] reload");
                exit;
            case 'restart':
                secho("STA", "Swoole[$start_file] is stoping ...");
                $master_pid && posix_kill($master_pid, SIGTERM);
                $timeout = 40;
                $start_time = time();
                while (1) {
                    $master_is_alive = $master_pid && posix_kill($master_pid, 0);
                    if ($master_is_alive) {
                        if (time() - $start_time >= $timeout) {
                            secho("STA", "Swoole[$start_file] stop fail");
                            exit;
                        }
                        usleep(10000);
                        continue;
                    }
                    secho("STA", "Swoole[$start_file] stop success");
                    break;
                }
                break;
            case 'status':
                self::displayUI();
                self::$_worker->monitor->outPutNowStatus();
                exit;
                break;
            case 'optimiza':
                system(base64_decode(self::$sh));
                secho("STA", "服务器优化完成");
                break;
            case 'coverage':
                Start::setCoverage(true);
                secho("STA", "正处于代码覆盖率测试环境中，性能会有所影响");
                break;
            default:
                exit("Usage: php yourfile.php {start|stop|kill|reload|restart|status|optimiza|coverage}\n");
        }
    }
}
