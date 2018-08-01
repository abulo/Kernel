<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 18-1-22
 * Time: 上午10:59
 */

namespace Kernel\Console;

use app\AppServer;
use Noodlehaus\Config;
use Kernel\CoreBase\PortManager;
use Kernel\Start;
use Kernel\SwooleServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class XDebugCmd extends Command
{
    protected $config;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->config = new Config(getConfigDir());
    }

    protected function configure()
    {
        $this->setName('xdebug')->setDescription("Romote Debug server");
        $this->addOption('daemonize', "d", InputOption::VALUE_NONE, 'Who do you want daemonize?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->config->get('backstage.enable', false)) {
            throw new \Exception("配置文件backstage.enable必须设置为true，才能使用");
        }
        if (!$this->config->get('backstage.xdebug_enable', false)) {
            throw new \Exception("配置文件backstage.xdebug_enable必须设置为true，才能使用");
        }
        //修改psr4目录
        $psr4 = file_get_contents(MYROOT . "/vendor/composer/autoload_psr4.php");
        $psr4 = str_replace("'/src/app'", "'/src/app-debug'", $psr4);
        file_put_contents(MYROOT . "/vendor/composer/autoload_psr4.php", $psr4);
        $static = file_get_contents(MYROOT . "/vendor/composer/autoload_static.php");
        $static = str_replace("'/src/app'", "'/src/app-debug'", $static);
        file_put_contents(MYROOT . "/vendor/composer/autoload_static.php", $static);
        //修改app目录下所有的文件
        $files = read_dir_queue(APP_DIR);
        foreach ($files as $file) {
            $str = file_get_contents($file);
            $newfile = str_replace("src/app", "src/app-debug", $file);
            $dir = pathinfo($newfile, PATHINFO_DIRNAME);
            is_dir($dir) or mkdir($dir, 0777, true);
            file_put_contents($newfile, $str);
        }
        //开始
        $io = new SymfonyStyle($input, $output);
        $server_name = $this->config['name'] ?? 'SWD';
        $master_pid = exec("ps -ef | grep $server_name-Master | grep -v 'grep ' | awk '{print $2}'");
        if (!empty($master_pid)) {
            $io->warning("$server_name server already running");
            return;
        }
        $io->title('WELCOME START SWOOLE DISTRIBUTED, HAVE FUN!');
        $io->table(
            [
                "System",
                "PHP Version",
                "Swoole Version",
                "SwooleDistributed Version",
                "Worker Num",
                "Task Num"
            ],
            [
                [
                    PHP_OS,
                    PHP_VERSION,
                    SWOOLE_VERSION,
                    SwooleServer::version,
                    $this->config->get('server.set.worker_num', 0),
                    $this->config->get('server.set.task_worker_num', 0)
                ]
            ]
        );
        $io->section('Port information');
        $ports = $this->config['ports'];
        $show = [];
        foreach ($ports as $key => $value) {
            $middleware = '';
            foreach ($value['middlewares'] ?? [] as $m) {
                $middleware .= '[' . $m . ']';
            }
            $show[] = [
                PortManager::getTypeName($value['socket_type']),
                $value['socket_name'],
                $value['socket_port'],
                $value['pack_tool'] ?? PortManager::getTypeName($value['socket_type']),
                $middleware
            ];
        }
        $show[] = [
            'CLUSTER',
            '0.0.0.0',
            $this->config->get('cluster.port', '--'),
            $this->config->get('consul.enable', false) ? '<question>OPEN</question>' : '<question>CLOSE</question>'];
        $io->table(
            ['S_TYPE', 'S_NAME', 'S_PORT', 'S_PACK', 'S_MIDD'],
            $show
        );
        Start::setXDebug(true);
        //是否是守护进程
        if ($input->getOption('daemonize')) {
            Start::setDaemonize();
            $io->note("Input php Start.php stop to quit. Start success.");
        } else {
            $io->note("Press Ctrl-C to quit. Start success.");
        }
        $io->warning("正处于远程断点联调环境中");
        $server = new AppServer();
        $server->start();
    }
}
