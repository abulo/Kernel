<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-9-1
 * Time: 下午4:25
 */

namespace Kernel\Asyn\Http;


use Kernel\CoreBase\Child;
use Kernel\Memory\Pool;
use Kernel\CoreBase\SwooleException;



class HttpClientRequest extends  Child
{
    /**
     * @var HttpClientPool
     */
    public $http_pool;
    /**
     * @var array
     */
    protected $activeConfig;
    /**
     * @var
     */
    protected $client;

    public $queryDate = [];

    /**
     * Miner constructor.
     * @param $http_pool
     */
    public function __construct($http_pool = null)
    {
        parent::__construct();
        $this->http_pool = $http_pool;
    }

    /**
     * @param $http_pool
     * @return $this
     */
    public function setPool(HttpClientPool $http_pool)
    {
        $this->core_name = $http_pool->getAsynName();
        $this->http_pool = $http_pool;
        $this->clear();
        return $this;
    }

    public function clear()
    {
        $this->queryData = [];
    }

    /**
     * 超时控制
     *
     * @param array $ary
     * @return this
     */
    public function set(array $set)
    {

        // $this->http_pool->set($set);
        $this->queryData['set'] = $set;
        // 'timeout' => 3.0
        // 连接超时或被服务器关闭连接，statusCode将设置为-1
        // 在约定的时间内服务器未返回响应，请求超时，statusCode将设置为-2
        // 请求超时后底层会自动切断连接
        // 设置为-1表示永不超时，底层将不会添加超时检测的定时器
        //'keep_alive' => false    ,设置keep_alive选项，启用或关闭Http长连接。
        //'websocket_mask' => true ,WebSocket客户端启用或关闭掩码。默认为关闭。启用后会对WebSocket客户端发送的数据使用掩码进行数据转换。
        return $this;
    }

    /**
     * 设置Http请求方法
     * $method 必须为符合Http标准的方法名称，如果$method设置错误可能会被Http服务器拒绝请求
     * setMethod 仅在当前请求有效，发送请求后会立刻清除method设置
     *
     * @return this
     */
    public function setMethod(string $method = 'get')
    {
        $this->queryData['setMethod'] = strtoupper($method);
        // $this->http_pool->setMethod(strtoupper($method));
        return $this;
    }

    /**
     * 设置Http请求头
     * $headers必须为键值对应的数组，底层会自动映射为$key: $value格式的Http标准头格式
     * setHeaders设置的Http头在Coroutine\Http\Client对象存活期间的每次请求永久有效
     * 重新调用setHeaders会覆盖上一次的设置
     * @param array $headers
     * @return this
     */
    public function setHeaders(array $headers)
    {
        // $this->http_pool->setHeaders($headers);
        $this->queryData['setHeaders'] = $headers;
        return $this;
    }

    /**
     * 设置Cookie
     * $cookies 设置COOKIE，必须为键值对应数组
     * 设置COOKIE后在客户端对象存活期间会持续保存
     * 服务器端主动设置的COOKIE会合并到cookies数组中，可读取$client->cookies属性获得当前Http客户端的COOKIE信息
     * @param array $cookies
     * @return this
     */
    public function setCookies(array $cookies)
    {
        // $this->http_pool->setCookies($cookies);
        $this->queryData['setCookies'] = $cookies;
        return $this;
    }

    /**
     * 设置Http请求的包体
     * $data 为字符串格式
     * 设置$data后并且未设置$method，底层会自动设置为POST
     * 未设置Http请求包体并且未设置$method，底层会自动设置为GET
     * @param string $data
     * @return this
     */
    public function setData($data)
    {
        // $this->http_pool->setData($data);
        $this->queryData['setData'] = $data;
        return $this;
    }


    /**
     * 设置Http请求的包体
     * $data 为字符串格式
     * 设置$data后并且未设置$method，底层会自动设置为POST
     * 未设置Http请求包体并且未设置$method，底层会自动设置为GET
     * @param string $data
     * @return this
     */
    public function setQuery($data)
    {
        $this->queryData['setQuery'] = $data;
        return $this;
    }
    /**
     * 添加POST文件
     *
     * @param string $path  文件的路径，必选参数，不能为空文件或者不存在的文件
     * @param string $name  表单的名称，必选参数，FILES参数中的key
     * @param string $mimeType 文件的MIME格式，可选参数，底层会根据文件的扩展名自动推断
     * @param string $filename 文件名称，可选参数，默认为basename($path)
     * @param integer $offset 上传文件的偏移量，可以指定从文件的中间部分开始传输数据。此特性可用于支持断点续传。
     * @param integer $length 发送数据的尺寸，默认为整个文件的尺寸
     * @return void
     */
    public function addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0)
    {
        // $this->http_pool->addFile($path,  $name, $mimeType,  $filename,  $offset,  $length);
        $this->queryData['addFile']['path'] = $path;
        $this->queryData['addFile']['name'] = $name;
        $this->queryData['addFile']['mimeType'] = $mimeType;
        $this->queryData['addFile']['filename'] = $filename;
        $this->queryData['addFile']['offset'] = $offset;
        $this->queryData['addFile']['length'] = $length;
        // $this->queryData['addFile'][] = $path;
        return $this;
    }

    /**
     * 使用字符串构建上传文件内容,使用addData会自动将POST的Content-Type将变更为form-data。
     *
     * @param string $data 数据内容，必选参数，最大长度不得超过buffer_output_size
     * @param string $name 表单的名称，必选参数，$_FILES参数中的key
     * @param string $mimeType 文件的MIME格式，可选参数，默认为application/octet-stream
     * @param string $filename 文件名称，可选参数，默认为$name
     * @return void
     */
    public function addData(string $data, string $name, string $mimeType = null, string $filename = null)
    {
        // $this->http_pool->addData($data,  $name,  $mimeType,  $filename);
        $this->queryData['addData']['data'] = $data;
        $this->queryData['addData']['name'] = $name;
        $this->queryData['addData']['mimeType'] = $mimeType;
        $this->queryData['addData']['filename'] = $filename;
        // $this->queryData['addData']['data'] = $data;
        // $this->queryData['addData']['data'] = $data;
        return $this;
    }

    /**
     * 通过Http下载文件
     *
     * @param string $path URL路径
     * @param string $filename 指定下载内容写入的文件路径，会自动写入到downloadFile属性
     * @param integer $offset 指定写入文件的偏移量，此选项可用于支持断点续传，可配合Http头Range:bytes=$offset-实现,为0时若文件已存在，底层会自动清空此文件
     * @return void
     * 执行成功返回true
     */
    public function download(string $path, string $filename,  int $offset = 0)
    {
        $this->queryData['download']['path'] = $path;
        $this->queryData['download']['filename'] = $filename;
        $this->queryData['download']['offset'] = $offset;
        return $this;
    }


    /**
     * 执行异步
     *
     * @return void
     */
    public function exec($path = null)
    {
        $this->queryData['exec']['path'] = $path;
        // $this->queryData['exec']['data'] = $data;
        // secho('$this->http_pool',$this->http_pool);
        $this->queryData['active'] = $this->http_pool->active;

        return $this->getProxy()->execute($this->queryData);
    }
    /**
     * Undocumented function
     *
     * @param string $path
     * @param array $data
     * @return void
     */
    public function execute($queryData)
    {
        $coroutine = Pool::getInstance()->get(HttpClientCoroutine::class);
        if (getInstance()->isTaskWorker()) {//如果是task进程自动转换为同步模式
            $this->clear();
            throw new SwooleException('暂时没有HttpClientPool的同步方法');
        } else {
            $coroutine->setRequest($queryData);
            //设置延时
            $coroutine->setDelayRecv();
            $this->clear();
            $result = $this->http_pool->execute($queryData, $coroutine);
            return $result;
        }
    }
}
