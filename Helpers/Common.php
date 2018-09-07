<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-7-15
 * Time: 上午11:38
 */
/**
 * 获取实例
 * @return \Kernel\SwooleDistributedServer
 */
function &getInstance()
{
    return \Kernel\SwooleDistributedServer::getInstance();
}

/**
 * 获取服务器运行到现在的毫秒数
 * @return int
 */
function getTickTime()
{
    return getMillisecond() - \Kernel\Start::getStartMillisecond();
}

/**
 * 获取当前的时间(毫秒)
 * @return float
 */
function getMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

function shell_read()
{
    $fp = fopen('php://stdin', 'r');
    $input = fgets($fp, 255);
    fclose($fp);
    $input = chop($input);
    return $input;
}

/**
 * http发送文件
 * @param $path
 * @param $response
 * @return mixed
 */
function httpEndFile($path, $request, $response)
{
    $path = urldecode($path);
    if (!file_exists($path)) {
        return false;
    }
    $lastModified = gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT';
    //缓存
    if (isset($request->header['if-modified-since']) && $request->header['if-modified-since'] == $lastModified) {
        $response->status(304);
        $response->end('');
        return true;
    }
    $extension = get_extension($path);
    $normalHeaders = getInstance()->config->get("fileHeader.normal", ['Content-Type: application/octet-stream']);
    $headers = getInstance()->config->get("fileHeader.$extension", $normalHeaders);
    foreach ($headers as $value) {
        list($hk, $hv) = explode(': ', $value);
        $response->header($hk, $hv);
    }
    $response->header('Last-Modified', $lastModified);
    $response->sendfile($path);
    return true;
}

/**
 * 获取后缀名
 * @param $file
 * @return mixed
 */
function get_extension($file)
{
    $info = pathinfo($file);
    return strtolower($info['extension'] ?? '');
}

/**
 * php在指定目录中查找指定扩展名的文件
 * @param $path
 * @param $ext
 * @return array
 */
function get_files_by_ext($path, $ext)
{
    $files = array();
    if (is_dir($path)) {
        $handle = opendir($path);
        while ($file = readdir($handle)) {
            if ($file[0] == '.') {
                continue;
            }
            if (is_file($path . $file) and preg_match('/\.' . $ext . '$/', $file)) {
                $files[] = $file;
            }
        }
        closedir($handle);
    }
    return $files;
}

function getLuaSha1($name)
{
    return \Kernel\Asyn\Redis\RedisLuaManager::getLuaSha1($name);
}

/**
 * 检查扩展
 * @return bool
 */
function checkExtension()
{
    $check = true;
    if (!extension_loaded('swoole')) {
        secho("STA", "[扩展依赖]缺少swoole扩展");
        $check = false;
    }
    if (extension_loaded('xhprof')) {
        secho("STA", "[扩展错误]不允许加载xhprof扩展，请去除");
        $check = false;
    }
    if (extension_loaded('xdebug')) {
        secho("STA", "[扩展错误]不允许加载xdebug扩展，请去除");
        $check = false;
    }
    if (version_compare(PHP_VERSION, '7.0.0', '<')) {
        secho("STA", "[版本错误]PHP版本必须大于7.0.0\n");
        $check = false;
    }
    if (SWOOLE_VERSION[0] == 1) {
        secho("STA", "[版本错误]不支持1.0版本swoole，请安装2.0以上版本");
        $check = false;
    }

    if (!class_exists('swoole_redis')) {
        secho("STA", "[编译错误]swoole编译缺少--enable-async-redis,具体参见文档http://docs.sder.xin/%E7%8E%AF%E5%A2%83%E8%A6%81%E6%B1%82.html");
        $check = false;
    }
    if (!extension_loaded('redis')) {
        secho("STA", "[扩展依赖]缺少redis扩展");
        $check = false;
    }
    if (!extension_loaded('pdo')) {
        secho("STA", "[扩展依赖]缺少pdo扩展");
        $check = false;
    }

    if (getInstance()->config->has('consul_enable')) {
        secho("STA", "consul_enable配置已被弃用，请换成['consul']['enable']");
        $check = false;
    }
    if (getInstance()->config->has('use_dispatch')) {
        secho("STA", "use_dispatch配置已被弃用，请换成['dispatch']['enable']");
        $check = false;
    }
    if (getInstance()->config->has('dispatch_heart_time')) {
        secho("STA", "dispatch_heart_time配置已被弃用，请换成['dispatch']['heart_time']");
        $check = false;
    }


    $dispatch_enable = getInstance()->config->get('dispatch.enable', false);
    if ($dispatch_enable) {
        if (!getInstance()->config->get('redis.enable', true)) {
            secho("STA", "开启dispatch，就必须启动redis的配置");
            $check = false;
        }
    }
    return $check;
}

/**
 * 是否是mac系统
 * @return bool
 */
function isDarwin()
{
    if (PHP_OS == "Darwin") {
        return true;
    } else {
        return false;
    }
}
function displayExceptionHandler(\Throwable $exception)
{
    getInstance()->log->error($exception->getMessage(), ["trace"=>$exception->getTrace()]);
    secho("EX", "------------------发生异常：".$exception->getMessage()."-----------------------");
    $string = $exception->getTraceAsString();
    $arr = explode("#", $string);
    unset($arr[0]);
    foreach ($arr as $value) {
        secho("EX", "#".$value);
    }
}
/**
 * 代替sleep
 * @param $ms
 * @return mixed
 */
function sleepCoroutine($ms)
{
    \co::sleep($ms / 1000);
}

/**
 * @param string $dev
 * @return string
 */
function getServerIp($dev = 'eth0')
{
    // return exec("ip -4 addr show $dev | grep inet | awk '{print $2}' | cut -d / -f 1");
    return exec("ifconfig $dev | grep \"inet addr:\" | awk '{print $2}' | cut -c 6-");
}

/**
 * @return string
 */
function getBindIp()
{
    return getInstance()->getBindIp();
}

/**
 * @return array|false|mixed|string
 */
function getNodeName()
{
    // global $node_name;
    // if (!empty($node_name)) {
    //     return $node_name;
    // }
    $env_SD_NODE_NAME = getenv("SD_NODE_NAME");
    if (!empty($env_SD_NODE_NAME)) {
        $node_name = $env_SD_NODE_NAME;
    } else {
        if (!isset(getInstance()->config['consul']['node_name'])
            || empty(getInstance()->config['consul']['node_name'])) {
            $node_name = exec('hostname');
        } else {
            $node_name = getInstance()->config['consul']['node_name'];
        }
    }
    return $node_name;
}

/**
 * @return mixed|string
 */
function getServerName()
{
    return getInstance()->config['name'] ?? 'SWD';
}

/**
 * @return string
 */
function getConfigDir()
{
    return CONFIG_PATH.DS.ENV;
}

/**
 * @param string $prefix
 * @return string
 */
function create_uuid($prefix = "")
{
    //可以指定前缀
    mt_srand();
    $str = md5(uniqid(mt_rand(), true));
    $uuid = substr($str, 0, 8) . '-';
    $uuid .= substr($str, 8, 4) . '-';
    $uuid .= substr($str, 12, 4) . '-';
    $uuid .= substr($str, 16, 4) . '-';
    $uuid .= substr($str, 20, 12);
    return $prefix . $uuid;
}

function print_context($context)
{
    secho("EX", "运行链路:");
    foreach ($context['RunStack'] as $key => $value) {
        secho("EX", "$key# $value");
    }
}

function secho($tile, $message)
{
    ob_start();
    if (is_string($message)) {
        $message = ltrim($message);
        $message = str_replace(PHP_EOL, '', $message);
    }
    print_r($message);
    $content = ob_get_contents();
    ob_end_clean();
    $could = false;
    if (empty(\Kernel\Start::getDebugFilter())) {
        $could = true;
    } else {
        foreach (\Kernel\Start::getDebugFilter() as $filter) {
            if (strpos($tile, $filter) !== false || strpos($content, $filter) !== false) {
                $could = true;
                break;
            }
        }
    }

    $content = explode("\n", $content);
    $send = "";
    foreach ($content as $value) {
        if (!empty($value)) {
            $echo = "[$tile] $value";
            $send = $send . $echo . "\n";
            if ($could) {
                echo " > $echo\n";
            }
        }
    }
    try {
        if (getInstance() != null) {
            getInstance()->pub('$SYS/' . getNodeName() . "/echo", $send);
        }
    } catch (Exception $e) {
    }
}

function setTimezone()
{
    date_default_timezone_set('Asia/Shanghai');
}

function format_date($time)
{
    $day = (int)($time / 60 / 60 / 24);
    $hour = (int)($time / 60 / 60) - 24 * $day;
    $mi = (int)($time / 60) - 60 * $hour - 60 * 24 * $day;
    $se = $time - 60 * $mi - 60 * 60 * $hour - 60 * 60 * 24 * $day;
    return "$day 天 $hour 小时 $mi 分 $se 秒";
}

function sd_call_user_func($function, ...$parameter)
{
    if (is_callable($function)) {
        return $function(...$parameter);
    }
}

function sd_call_user_func_array($function, $parameter)
{
    if (is_callable($function)) {
        return $function(...$parameter);
    }
}

/**
 * @param $arr
 * @throws \Kernel\Asyn\MQTT\Exception
 */
function sd_debug($arr)
{
    Server\Components\SDDebug\SDDebug::debug($arr);
}

function read_dir_queue($dir)
{
    $files = array();
    $queue = array($dir);
    while ($data = each($queue)) {
        $path = $data['value'];
        if (is_dir($path) && $handle = opendir($path)) {
            while ($file = readdir($handle)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $files[] = $real_path = realpath($path . '/' . $file);
                if (is_dir($real_path)) {
                    $queue[] = $real_path;
                }
            }
        }
        closedir($handle);
    }
    $result = [];
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
            $result[] = $file;
        }
    }
    return $result;
}
