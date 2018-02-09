<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午3:11
 */

namespace Kernel\Route;

use Kernel\CoreBase\SwooleException;
use FastRoute;

class FRoute implements IRoute
{
    private $client_data;


    public function __construct()
    {
        $this->client_data = new \stdClass();
    }






    /**
     * 设置反序列化后的数据 Object
     * @param $data
     * @return \stdClass
     * @throws SwooleException
     */
    public function handleClientData($data)
    {
        $this->client_data = $data;
        if (isset($this->client_data->controller_name) && isset($this->client_data->method_name)) {
            return $this->client_data;
        } else {
            throw new SwooleException('route 数据缺少必要字段');
        }
    }

    /**
     * 处理http request
     * @param $request
     */
    public function handleClientRequest($request)
    {





        $requestUri = $request->server['request_uri'];
        $requestMethod = $request->server['request_method'];
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        $requestUri = rawurldecode($requestUri);
        $info = getInstance()->getDispatcher()->dispatch($requestMethod, $requestUri);
        switch ($info[0]) {
            case FastRoute\Dispatcher::NOT_FOUND://0
                throw new SwooleException('没有找到路由404');
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED://2
                throw new SwooleException('没有权限访问403');
                break;
            case FastRoute\Dispatcher::FOUND:
                $route = explode('@', $info[1]);

                $this->client_data->path = $requestUri;

                $this->client_data->controller_name = $route[0];
                $this->client_data->method_name = $route[1];
                $this->client_data->handler = $info[1];
                $this->client_data->params = $info[2];
                break;
        }
    }

    public function getHandler()
    {
        return $this->client_data->handler;
    }





    public function getMiddlewareMethodName()
    {
        return $this->client_data->middleware_method_name;
    }

    /**
     * 获取控制器名称
     * @return string
     */
    public function getControllerName()
    {
        return $this->client_data->controller_name;
    }

    /**
     * 获取方法名称
     * @return string
     */
    public function getMethodName()
    {
        return $this->client_data->method_name;
    }

    public function getPath()
    {
        return $this->client_data->path ?? "";
    }

    public function getParams()
    {
        return $this->client_data->params??null;
    }

    public function errorHandle(\Exception $e, $fd)
    {
        getInstance()->send($fd, "Error:" . $e->getMessage(), true);
        getInstance()->close($fd);
    }

    public function errorHttpHandle(\Exception $e, $request, $response)
    {
        //重定向到404
        $response->status(302);
        $location = 'http://' . $request->header['host'] . "/" . '404';
        $response->header('Location', $location);
        $response->end('');
    }
}
