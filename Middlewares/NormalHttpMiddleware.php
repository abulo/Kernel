<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 17-9-28
 * Time: 下午2:45
 */

namespace Kernel\Middlewares;

use Kernel\Components\Middleware\HttpMiddleware;

class NormalHttpMiddleware extends HttpMiddleware
{
    protected static $cache404;

    public function __construct()
    {
        parent::__construct();
        if (NormalHttpMiddleware::$cache404 == null) {
            $template = getInstance()->loader->view(KERNEL_PATH.DS.'Views'.DS.'error_404');
            NormalHttpMiddleware::$cache404 = $template->render();
        }
    }

    public function before_handle()
    {
        // list($host) = explode(':', $this->request->header['host'] ?? '');
        // $path = $this->request->server['request_uri'];
        // if ($path == '/404') {
        //     $this->response->status(404);
        //     $this->response->header('HTTP/1.1', '404 Not Found');
        //     $this->response->end(NormalHttpMiddleware::$cache404);
        //     $this->interrupt();
        // }
        // $extension = pathinfo($path, PATHINFO_EXTENSION);
        // if ($path == "/") {//寻找主页
        //     $www_path = $this->getHostRoot($host) . $this->getHostIndex($host);
        //     $result = httpEndFile($www_path, $this->request, $this->response);
        //     if (!$result) {
        //      $this->response->status(404);
        //         $this->response->header('HTTP/1.1', '404 Not Found');
        //         $this->response->end(NormalHttpMiddleware::$cache404);
        //         $this->interrupt();
        //         // $this->redirect404();
        //     } else {
        //         $this->interrupt();
        //     }
        // } elseif (!empty($extension)) {//有后缀
        //     $www_path = $this->getHostRoot($host) . $path;
        //     $result = httpEndFile($www_path, $this->request, $this->response);
        //     if (!$result) {
        //      $this->response->status(404);
        //         $this->response->header('HTTP/1.1', '404 Not Found');
        //         $this->response->end(NormalHttpMiddleware::$cache404);
        //         $this->interrupt();
        //         // $this->redirect404();
        //     } else {
        //         $this->interrupt();
        //     }
        // }
    }

    public function after_handle($path)
    {
        // TODO: Implement after_handle() method.
    }
}
