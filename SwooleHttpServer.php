<?php
/**
 * 包含http服务器
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-29
 * Time: 上午9:42
 */

namespace Kernel;

use Kernel\Layout\Engine;
use Kernel\Components\Consul\ConsulHelp;
use Kernel\CoreBase\ControllerFactory;
use Kernel\Coroutine\Coroutine;
use FastRoute;

abstract class SwooleHttpServer extends SwooleServer
{
    /**
     * 模板引擎
     * @var Engine
     */
    public $templateEngine;
    protected $cache404;



    public $router = null;
    public $routes = [];
    public $routeMapName= [];
    public $routeMapUrl= [];
    public $nameMapRoute= [];
    public $middleware = [];
    public $middlewareMap = [];




    public function __construct()
    {
        parent::__construct();
    }



    public function initializeRoute()
    {
        $this->router = $this->createRouter();
        $this->registerRoutes();
        $this->registerMiddleware();
    }



    /**
     * FastRoute
     * @return FastRoute
     */
    protected function createRouter()
    {
        return getInstance()->container->make(
            'FastRoute\RouteCollector',
            [
                new FastRoute\RouteParser\Std(),
                new FastRoute\DataGenerator\GroupCountBased()
            ]
        );


        // return  new FastRoute\RouteCollector(
        //     new FastRoute\RouteParser\Std(),
        //     new FastRoute\DataGenerator\GroupCountBased()
        // );
    }



    public function getDispatcher()
    {
        return getInstance()->container->make(
            'FastRoute\Dispatcher\GroupCountBased',
            [
                $this->router->getData()
            ]
        );

        // return new FastRoute\Dispatcher\GroupCountBased(
        //     $this->router->getData()
        // );
    }




    /**
     * Middleware.php
     * @return Middleware
     */
    protected function registerMiddleware()
    {
        $files = glob(MIDDLEWARE_PATH.DS."*.middleware.php");
        foreach ($files as $file) {
            $middleware = is_file($file) ? include_once $file : array();
            if (is_array($middleware)) {
                $this->middleware = array_merge($this->middleware, $middleware);
            }
        }
    }



    /**
     * 解析middleware
     * @param  string $router
     * @return middleware or bool
     */
    // public function dispatchMiddleware($router)
    // {
    //     if (!isset($this->middleware[$router])) {
    //         return false;
    //     }
    //     return $this->middleware[$router];
    // }




    /**
     * route.php
     * @return routes
     */
    protected function registerRoutes()
    {
        $files = glob(ROUTE_PATH.DS."*.route.php");
        foreach ($files as $file) {
            $routes = is_file($file) ? include_once $file : array();
            if (is_array($routes)) {
                $this->routes = array_merge($this->routes, $routes);
            }
        }
        if (is_array($this->routes)) {
            foreach ($this->routes as $value) {
                if (!is_array($value[0]) && is_array($value[1])) {
                    $this->group($value[0], $value[1]);
                    $this->group($value);
                } else {
                    // $this->route($value[0], $value[1], $value[2], $value[3]);
                    $this->route($value);
                }
            }
        }
    }





    /**
     * FastRoute addRoute
     * @param  string $method
     * @param  string $route
     * @param  string $handler
     * @return
     */
     // protected function route($method, $route, $handler, $routeAlias)
    protected function route($val)
    {

        if (isset($val[4])) {
            @list($method, $route, $handler, $routeAlias,$middleware) = $val;
        } else {
            @list($method, $route, $handler, $routeAlias) = $val;
            $middleware = null;
        }



        $this->router->addRoute($method, $route, $handler);
        $this->routeMapName[$routeAlias] = $handler;
        $this->routeMapUrl[$routeAlias] = $route;
        $this->nameMapRoute[$handler] = $routeAlias;
        //如果 middleware 不为空
        if ($middleware) {
            $this->middlewareMap[$handler] = $middleware;
        }
        return $this;
    }
    /**
     * FastRoute addRoute
     * @param  string $method
     * @param  string $route
     * @param  string $handler
     * @return
     */
     // protected function group($group, $routes)
    protected function group($g)
    {
        if (isset($g[2])) {
            @list($group, $routes ,$middleware) = $g;
        } else {
            @list($group, $routes ) = $g;
            $middleware = null;
        }

        $this->router->addGroup($group, function (FastRoute\RouteCollector $router) use ($group, $routes, $middleware) {
            if (is_array($routes)) {
                foreach ($routes as $item) {
                    if (isset($item[4])) {
                        @list($method, $route, $handler,$routeAlias,$r_middleware) = $item;
                    } else {
                        @list($method, $route, $handler,$routeAlias) = $item;
                        $r_middleware = null;
                    }


                    $router->addRoute($method, $route, $handler);
                    $this->routeMapName[$routeAlias] = $handler;
                    $this->nameMapRoute[$handler] = $routeAlias;
                    $this->routeMapUrl[$routeAlias] = $group.$route;

                    if ($middleware) {
                        $this->middlewareMap[$handler] = $middleware;
                    }

                    if ($r_middleware) {
                        $this->middlewareMap[$handler] = $r_middleware;
                    }
                }
            }
        });
        return $this;
    }

    /**
     * 启动
     */
    public function start()
    {
        if (!$this->portManager->http_enable) {
            parent::start();
            return;
        }
        $first_config = $this->portManager->getFirstTypePort();
        $set = $this->portManager->getProbufSet($first_config['socket_port']);
        if (array_key_exists('ssl_cert_file', $first_config)) {
            $set['ssl_cert_file'] = $first_config['ssl_cert_file'];
        }
        if (array_key_exists('ssl_key_file', $first_config)) {
            $set['ssl_key_file'] = $first_config['ssl_key_file'];
        }
        $socket_ssl = $first_config['socket_ssl'] ?? false;
        //开启一个http服务器
        if ($socket_ssl) {
            $this->server = new \swoole_http_server($first_config['socket_name'], $first_config['socket_port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
        } else {
            $this->server = new \swoole_http_server($first_config['socket_name'], $first_config['socket_port']);
        }
        $this->setServerSet($set);
        $this->server->on('Start', [$this, 'onSwooleStart']);
        $this->server->on('WorkerStart', [$this, 'onSwooleWorkerStart']);
        $this->server->on('WorkerStop', [$this, 'onSwooleWorkerStop']);
        $this->server->on('Task', [$this, 'onSwooleTask']);
        $this->server->on('Finish', [$this, 'onSwooleFinish']);
        $this->server->on('PipeMessage', [$this, 'onSwoolePipeMessage']);
        $this->server->on('WorkerError', [$this, 'onSwooleWorkerError']);
        $this->server->on('ManagerStart', [$this, 'onSwooleManagerStart']);
        $this->server->on('ManagerStop', [$this, 'onSwooleManagerStop']);
        $this->server->on('request', [$this, 'onSwooleRequest']);
        $this->server->on('Shutdown', [$this, 'onSwooleShutdown']);
        $this->portManager->buildPort($this, $first_config['socket_port']);
        $this->beforeSwooleStart();
        $this->server->start();
    }

    /**
     * workerStart
     * @param $serv
     * @param $workerId
     */
    public function onSwooleWorkerStart($serv, $workerId)
    {
        parent::onSwooleWorkerStart($serv, $workerId);
        $this->initializeRoute();
        $this->setTemplateEngine();
        $template = $this->loader->view(KERNEL_PATH.DS.'Views'.DS.'error_404');
        $this->cache404 = $template->render();
    }

    /**
     * 设置模板引擎
     */
    public function setTemplateEngine()
    {
        $this->templateEngine = new Engine();
    }

    /**
     * http服务器发来消息
     * @param $request
     * @param $response
     */
    public function onSwooleRequest($request, $response)
    {
        //规整 URL 数据
        $request = $this->beforeSwooleHttpRequest($request);
        $server_port = $this->getServerPort($request->fd);
        Coroutine::startCoroutine(function () use ($request, $response, $server_port) {
            $middleware_names = $this->portManager->getMiddlewares($server_port);
            $context = [];
            $path = $request->server['request_uri'];
            $middlewares = $this->middlewareManager->create($middleware_names, $context, [$request, $response], true);
            //before
            try {
                yield $this->middlewareManager->before($middlewares);
                //client_data进行处理
                $route = $this->portManager->getRoute($server_port);
                try {
                    $route->handleClientRequest($request);

                    $cHandler = $route->getHandler();
                    $path = $route->getPath();
                    $middleware_route = $route->getMiddleware();
                    //路由上的中间件
                    if ($middleware_route) {
                        $middleware_controller_name = $route->getMiddlewareControllerName();
                        $middleware_method_name = $this->portManager->getMethodPrefix($server_port) . $route->getMiddlewareMethodName();
						//
                        $middleware_controller_instance = ControllerFactory::getInstance()->getController($middleware_controller_name);
                        if ($middleware_controller_instance != null) {
                            $request->route = $cHandler;
                            $middleware_result = yield $middleware_controller_instance->setRequestResponse($request, $response, $middleware_controller_name, $middleware_method_name, []);
                            if ($middleware_result!=null) {
                                $response->status($middleware_result['status']);
                                $response->header($middleware_result['header'][0], $middleware_result['header'][1]);
                                $response->end($middleware_result['content']);
                                $middleware_controller_instance->destroy();
                                return ;
                            }
                        } else {
                            throw new \Exception('no controller');
                        }
                    }

                    $controller_name = $route->getControllerName();
                    $method_name = $this->portManager->getMethodPrefix($server_port) . $route->getMethodName();

                    $controller_instance = ControllerFactory::getInstance()->getController($controller_name);

                    if ($controller_instance != null) {
                        $controller_instance->setContext($context);
                        if ($route->getMethodName() == ConsulHelp::HEALTH) {//健康检查
                            $response->end('ok');
                            $controller_instance->destroy();
                        } else {
                            $request->route = $cHandler;
                            yield $controller_instance->setRequestResponse($request, $response, $controller_name, $method_name, $route->getParams());
                        }
                    } else {
                        throw new \Exception('no controller');
                    }
                } catch (\Exception $e) {
                    $route->errorHttpHandle($e, $request, $response);
                }
            } catch (\Exception $e) {
            }
            //after
            try {
                yield $this->middlewareManager->after($middlewares, $path);
            } catch (\Exception $e) {
            }
            $this->middlewareManager->destory($middlewares);
            unset($context);
        });
    }



    /**
     * 规整数据
     * @param  SwooleHttpRequest $swooleHttpRequest
     * @return
     */
    private function beforeSwooleHttpRequest($swooleHttpRequest)
    {
        $request_uri = $swooleHttpRequest->server['request_uri'];
        $path_info = $swooleHttpRequest->server['path_info'];
        $request_uri = preg_replace('/\/{2,}/', '/', $request_uri);
        $path_info = preg_replace('/\/{2,}/', '/', $path_info);
        $request_uri = preg_replace('#/$#', '', $request_uri);
        $path_info = preg_replace('#/$#', '', $path_info);
        if (empty($request_uri)) {
            $request_uri = '/';
        }
        if (empty($path_info)) {
            $path_info = '/';
        }
        $swooleHttpRequest->server['request_uri'] = $request_uri;
        $swooleHttpRequest->server['path_info'] = $path_info;
        //记录客户端的 ID
        $client_id = $swooleHttpRequest->cookie['client_id']??create_uuid();
        $swooleHttpRequest->cookie['client_id'] = $client_id;
        return $swooleHttpRequest;
    }
}
