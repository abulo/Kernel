<?php

namespace Kernel\Components\WeChat\Kernel\Http;

use Kernel\Components\WeChat\Kernel\Support\XML;

/**
 * Class Response
 *
 * @package \Kernel\Components\WeChat\Kernel\Http
 */
class Response
{
    private $headers;
    private $body;
    private $statusCode;

    public function __construct(array $responseData)
    {
        $this->statusCode = (int) $responseData['statusCode'];

        $this->body = $responseData['body'];

        $this->headers = $responseData['headers'];
    }

    public function getHeader($header)
    {
        return $this->headers[strtolower($header)];
    }

    public function getBody()
    {
        return $this->body;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function toArray()
    {
        $content = $this->body;

        if (false !== stripos($this->getHeader('Content-Type'), 'xml') || 0 === stripos($content, '<xml')) {
            return XML::parse($content);
        }

        $array = json_encode($content, true);

        if (JSON_ERROR_NONE === json_last_error()) {
            return (array) $array;
        }

        return [];
    }

    public function __toString()
    {
        return (string) $this->body;
    }
}
