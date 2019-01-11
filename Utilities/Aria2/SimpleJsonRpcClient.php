<?php

namespace Kernel\Utilities\Aria2;

use \Curl\Curl;

class SimpleJsonRpcClient
{
    protected $url = '';
    public function __construct(string $url)
    {
        $this->url = $url;
    }
    public function __call(string $name, array $args)
    {
        return $this->call($name, ...$args);
    }
    public function call(string $name, ...$args)
    {
        $guid = strtoupper(create_uuid());
        $json = [
            'jsonrpc' => '2.0',
            'id'      => $guid,
            'method'  => $name,
            'params'  => $args,
        ];
        try {
            $curl = new Curl();
            $curl->setDefaultJsonDecoder($assoc = true);
            $curl->setHeader('Content-Type', 'application/json');
            $curl->post($this->url, $json);
            $curl->close();
        } catch (\Exception $e) {
            return [
                'err' => 1,
                'msg' => '下载服务器未启动'
            ];
        }
        $response = $curl->response;
        if (!isset($response['result'])) {
            return [
                'err' => 1,
                'msg' => '下载服务器未启动'
            ];
        }
        return $response['result'];
    }
}
