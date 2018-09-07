<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-7-29
 * Time: 上午11:02
 */

namespace Kernel\CoreBase;

use Kernel\Components\Session\Session;

class HttpInput
{
    /**
     * http request
     * @var \swoole_http_request
     */
    public $request;

    public $session;

    /**
     * @param $request
     */
    public function set($request)
    {
        $this->request = $request;
        $this->session = new Session($this->request->cookie['client_id']);
    }

    public function getRoute()
    {
        return $this->request->route;
    }

    public function getRouteName()
    {
        $route = $this->getRoute();
        return getInstance()->nameMapRoute[$route];
    }

    /**
     * 重置
     */
    public function reset()
    {
        unset($this->request);
    }

    /**
     * postGet
     * @param $index
     * @param $xss_clean
     * @return string
     */
    public function postGet($index, $default = null, $use_xss = true)
    {
        return isset($this->request->post[$index])
            ? $this->post($index, $default, $use_xss)
            : $this->get($index, $default, $use_xss);
    }

    /**
     * post
     * @param $index
     * @param $xss_clean
     * @return string
     */
    public function post($index, $default = null, $use_xss = true)
    {
        if ($use_xss) {
            return XssClean::getXssClean()->xss_clean($this->request->post[$index]??$default);
        } else {
            return $this->request->post[$index]??$default;
        }
    }

    /**
     * get
     * @param $index
     * @param $xss_clean
     * @return string
     */
    public function get($index, $default = null, $use_xss = true)
    {
        if ($use_xss) {
            return XssClean::getXssClean()->xss_clean($this->request->get[$index]??$default);
        } else {
            return $this->request->get[$index]??$default;
        }
    }

    /**
     * getPost
     * @param $index
     * @param $xss_clean
     * @return string
     */
    public function getPost($index, $default = null, $use_xss = true)
    {
        return isset($this->request->get[$index])
            ? $this->get($index, $default, $use_xss)
            : $this->post($index, $default, $use_xss);
    }

    /**
     * 获取所有的post
     */
    public function getAllPost($use_xss = true)
    {
        $result = [];
        if (is_array($this->request->post)) {
            foreach ($this->request->post as $item => $val) {
                if ($use_xss) {
                    $result[$item] = XssClean::getXssClean()->xss_clean($val);
                } else {
                    $result[$item] = $val;
                }
            }
        }
        return $result;
    }

    /**
     * 获取所有的get
     */
    public function getAllGet($use_xss = true)
    {
        $result = [];

        if (is_array($this->request->get)) {
            foreach ($this->request->get as $item => $val) {
                if ($use_xss) {
                    $result[$item] = XssClean::getXssClean()->xss_clean($val);
                } else {
                    $result[$item] = $val;
                }
            }
        }
        return $result;

        // return $this->request->get ?? [];
    }
    /**
     * 获取所有的post和get
     */
    public function getAllPostGet()
    {
        return array_merge($this->getAllPost() ?? [], $this->getAllGet() ?? []);
    }

    /**
     * @param $index
     * @param bool $xss_clean
     * @return array|bool|string
     */
    public function header($index, $default = null, $use_xss = true)
    {
        if ($use_xss) {
            return XssClean::getXssClean()->xss_clean($this->request->header[$index]??$default);
        } else {
            return $this->request->header[$index]??$default;
        }
    }

    /**
     * getAllHeader
     * @return array
     */
    public function getAllHeader()
    {
        return $this->request->header;
    }

    /**
     * 获取原始的POST包体
     * @return mixed
     */
    public function getRawContent()
    {
        return $this->request->rawContent();
    }

    /**
     * cookie
     * @param $index
     * @param $xss_clean
     * @return string
     */
    public function cookie($index, $default = null)
    {
        return $this->request->cookie[$index]??$default;
    }

    /**
     * getRequestHeader
     * @param $index
     * @param $xss_clean
     * @return string
     */
    public function getRequestHeader($index, $default = null)
    {
        return $this->request->header[$index]??$default;
    }

    /**
     * 获取Server相关的数据
     * @param $index
     * @param bool $xss_clean
     * @return array|bool|string
     */
    public function server($index, $default = null)
    {
        return $this->request->server[$index]??$default;
    }

    /**
     * @return mixed
     */
    public function getRequestMethod()
    {
        return $this->request->server['request_method'];
    }

    /**
     * @return mixed
     */
    public function getRequestUri()
    {
        if (array_key_exists('query_string', $this->request->server)) {
            return $this->request->server['request_uri'] . "?" . $this->request->server['query_string'];
        } else {
            return $this->request->server['request_uri'];
        }
    }

    /**
     * @return mixed
     */
    public function getPathInfo()
    {
        return $this->request->server['request_uri'];
    }

    /**
     * 文件上传信息
     * Array
     * (
     *   [name] => facepalm.jpg
     *   [type] => image/jpeg
     *   [tmp_name] => /tmp/swoole.upfile.n3FmFr
     *   [error] => 0
     *   [size] => 15476
     * )
     * @return mixed
     */
    public function getFiles()
    {
        return $this->request->files;
    }


    public function getIp()
    {



        if ($this->getRequestHeader('x-forwarded-for')) {
            $tmp = explode(',', $this->getRequestHeader('x-forwarded-for'));
            return $tmp[0];
        } elseif ($this->getRequestHeader('x-real-ip')) {
            return $this->getRequestHeader('x-real-ip');
        }

        // return $this->getRequestHeader('x-real-ip', '0.0.0.0');
    }
}
