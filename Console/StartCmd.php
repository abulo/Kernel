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

class StartCmd extends Command
{
    protected $config;

    /**
     * StartCmd constructor.
     * @param null $name
     * @throws \Noodlehaus\Exception\EmptyDirectoryException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->config = new Config(getConfigDir());
    }

    protected function configure()
    {
        $this->setName('start')->setDescription("Start server");
        $this->addOption('daemonize', "d", InputOption::VALUE_NONE, 'Who do you want daemonize?');
        $this->addOption('debug', null, InputOption::VALUE_NONE, 'Who do you want debug');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //修复debug修改的文件
        //修改psr4目录
        $psr4 = file_get_contents(MYROOT."/vendor/composer/autoload_psr4.php");
        $psr4 = str_replace("'/src/app-debug'", "'/src/app'", $psr4);
        file_put_contents(MYROOT."/vendor/composer/autoload_psr4.php", $psr4);
        $static = file_get_contents(MYROOT."/vendor/composer/autoload_static.php");
        $static = str_replace("'/src/app-debug'", "'/src/app'", $static);
        file_put_contents(MYROOT."/vendor/composer/autoload_static.php", $static);
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
        //是否是守护进程
        if ($input->getOption('daemonize')) {
            Start::setDaemonize();
            $io->note("Input php Start.php stop to quit. Start success.");
        } else {
            $io->note("Press Ctrl-C to quit. Start success.");
        }
        $server = new AppServer();
        //是否Debug
        if ($input->getOption('debug')) {
            Start::setDebug(true);
        }
        $server->start();
    }
}
