<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-7-25
 * Time: 上午10:29
 */

namespace Kernel;

use Kernel\CoreBase\PortManager;
use FastRoute;

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

    /**
     * Run all worker instances.
     *
     * @return void
     */
    public static function run($daemonize = false)
    {
        self::$debug = new \swoole_atomic(0);
        self::$leader = new \swoole_atomic(0);
        self::$startTime = date('Y-m-d H:i:s');
        self::$startMillisecond = getMillisecond();
        self::$daemonize = $daemonize;
        if(!$daemonize)
        {
            self::setDebug(1);
        }
        self::checkSapiEnv();
        self::init();
        self::parseCommand();
        self::initWorkers();
        self::startSwoole();
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
            exit("Usage: php yourfile.php {start|stop|kill|reload|restart|status}\n");
        }
        // Get command.
        $command = trim($argv[1]);
        //主进程
        $master_pid = ServerPid::getMasterPid(self::$_worker->pidFilePath);
        //管理进程
        $manager_pid = ServerPid::getManagerPid(self::$_worker->pidFilePath);


        $server_name = getServerName();
        if (!$master_pid) {
            $master_pid = exec("ps -ef | grep $server_name:master | grep -v 'grep ' | awk '{print $2}'");
        }
        if (!$manager_pid) {
            $manager_pid = exec("ps -ef | grep $server_name:manager | grep -v 'grep ' | awk '{print $2}'");
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
                // Send stop signal to master process.
                $master_pid && posix_kill($master_pid, SIGTERM);
                // Timeout.
                $timeout = 40;
                $start_time = time();
                // Check master process is still alive?
                while (1) {
                    $master_is_alive = $master_pid && posix_kill($master_pid, 0);
                    if ($master_is_alive) {
                        // Timeout?
                        if (time() - $start_time >= $timeout) {
                            secho("STA", "Swoole[$start_file] stop fail");
                            exit;
                        }
                        // Waiting amoment.
                        usleep(10000);
                        continue;
                    }
                    // Stop success.
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
                // Send stop signal to master process.
                $master_pid && posix_kill($master_pid, SIGTERM);
                // Timeout.
                $timeout = 40;
                $start_time = time();
                // Check master process is still alive?
                while (1) {
                    $master_is_alive = $master_pid && posix_kill($master_pid, 0);
                    if ($master_is_alive) {
                        // Timeout?
                        if (time() - $start_time >= $timeout) {
                            secho("STA", "Swoole[$start_file] stop fail");
                            exit;
                        }
                        // Waiting amoment.
                        usleep(10000);
                        continue;
                    }
                    // Stop success.
                    secho("STA", "Swoole[$start_file] stop success");
                    break;
                }
                break;
            case 'status':
                self::displayUI();
                self::$_worker->monitor->outPutNowStatus();
                exit;
                break;
            default:
                exit("Usage: php yourfile.php {start|stop|kill|reload|restart|status}\n");
        }
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
        echo 'SwooleDistributed version:', SwooleServer::version, "\n";
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

                    $active = exec("netstat -nlp|grep ':".$value['socket_port']."'| grep -v 'grep' | awk '{print $4}'");
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

                $active = exec("netstat -nlp|grep ':".self::$_worker->config->get('cluster.port', '--')."'| grep -v 'grep' | awk '{print $4}'");
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
}
