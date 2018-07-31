<?php

namespace Kernel\CoreBase;

/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-7-29
 * Time: 上午11:22
 */
class HttpOutput
{
    /**
     * http response
     * @var \swoole_http_response
     */
    public $response;

    /**
     * http request
     * @var \swoole_http_request
     */
    public $request;
    /**
     * @var Controller
     */
    protected $controller;

    /**
     * HttpOutput constructor.
     * @param $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /**
     * 设置
     * @param $request
     * @param $response
     */
    public function set($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * 重置
     */
    public function reset()
    {
        unset($this->response);
        unset($this->request);
    }

    /**
     * Set HTTP Status Header
     *
     * @param    int    the status code
     * @param    string
     * @return HttpOutPut
     */
    public function setStatusHeader($code = 200)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        $this->response->status($code);
        return $this;
    }

    /**
     * Set Content-Type Header
     *
     * @param    string $mime_type Extension of the file we're outputting
     * @return    HttpOutPut
     */
    public function setContentType($mime_type)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        $this->setHeader('Content-Type', $mime_type);
        return $this;
    }

    /**
     * set_header
     * @param $key
     * @param $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        $this->response->header($key, $value);
        return $this;
    }


    public function setHeaders(array $headers)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }
        return $this;
    }


    public function html($output = '', $gzip = true)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
        // $this->clientId();
        $this->end($output, $gzip);
        return;
    }




    public function xml($output = '', $gzip = true)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        $this->setHeader('Content-Type', 'application/xml; charset=UTF-8');
        // $this->clientId();
        $this->end($output, $gzip);
        return;
    }



    public function json($output = '', $gzip = true)
    {
        $this->setHeader('Content-Type', 'application/json; charset=UTF-8');
        // $this->clientId();
        $this->end(json_encode($output), $gzip);
        return;
    }
    /**
     * 中间件
     * @param  string $output
     * @return
     */
    public function middleware_end_json($output = null, $code = 200)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        // $this->clientId();
        if (null == $output) {
            return $output;
        }
        $response = [
            'status' => $code,
            'header' => ['Content-Type','application/json; charset=UTF-8'],
            'content'=> json_encode($output),
        ];
        return $response;
    }

    /**
     * 中间件
     * @param  string $output
     * @return
     */
    public function middleware_end_html($output = null, $code = 200)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        // $this->clientId();
        if (null == $output) {
            return $output;
        }
        $response = [
            'status' => $code,
            'header' => ['Content-Type','text/html; charset=UTF-8'],
            'content'=> $output,
        ];
        return $response;
    }



    public function middleware_end_xml($output = null, $code = 200)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        // $this->clientId();
        if (null == $output) {
            return $output;
        }
        $response = [
            'status' => $code,
            'header' => ['Content-Type','application/xml; charset=UTF-8'],
            'content'=> $output,
        ];
        return $response;
    }

    /**
     * 发送
     * @param string $output
     * @param bool $gzip
     */
    public function end($output = '', $gzip = true)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        if (!getInstance()->config->get('http.gzip_off', false)) {
            //低版本swoole的gzip方法存在效率问题
            if ($gzip) {
                $this->response->gzip(1);
            }
        }
        if (is_array($output)||is_object($output)) {
            $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
            $output = json_encode($output, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
            $output = "<pre>$output</pre>";
        }
        $this->clientId();
        $this->response->end($output);
        $this->controller->endOver();
    }


    /**
     * 设置HTTP响应的cookie信息。此方法参数与PHP的setcookie完全一致。
     * @param string $key
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     */
    public function setCookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }



    public function setCookies(array $cookie)
    {
        if (!$this->controller->canEnd()) {
            return;
        }
        foreach ($cookie as $k => $v) {
            list($key, $value, $expire, $path , $domain , $secure, $httponly) = $v;
            $this->setCookie($key, $value, $expire, $path, $domain, $secure, $httponly);
        }
        return $this;
    }

    /**
     * 输出文件
     * @param $file_path
     * @return mixed
     */
    public function endFile($file_path)
    {
        if (!$this->controller->canEnd()) {
            return null;
        }
        $result = httpEndFile($file_path, $this->request, $this->response);
        $this->controller->endOver();
        return $result;
    }

    /**
     * 给客户端返回 client_id
     * @var [type]
     */
    public function clientId()
    {
        $client = $this->request->cookie['client_id'] ?? 0;
        if ($client) {
            $this->setCookie('client_id', $client, time()+86400, '/', '', false, true);
        }
    }
}
