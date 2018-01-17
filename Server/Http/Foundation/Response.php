<?php

namespace Kernel\Server\Http\Foundation;

use Kernel\Utilities\Arr;
use Kernel\Utilities\Str;
use \Swoole\Http\Response as SwooleHttpResponse;

/**
 * Class Request
 *
 * @package \App
 */
class Response
{
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    /**
     * Status codes translation table.
     *
     * @var array
     */
    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    /**
     * @var
     */
    public $headers;
    /**
     * @var
     */
    public $cookies;
    /**
     * @var string
     */
    protected $content;
    /**
     * @var int
     */
    protected $statusCode;
    /**
     * @var string
     */
    protected $statusText;

    /**
     * Constructor.
     * @param mixed $content The response content, see setContent()
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = $headers;
        $this->setContent($content);
        $this->setStatusCode($status);
    }

    /**
     * @param mixed $content Content that can be cast to string
     * @return BaseResponse
     * @throws \UnexpectedValueException
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Sets the response status code.
     * @param int   $code HTTP status code
     * @param mixed $text HTTP status text
     * @return BaseResponse
     */
    public function setStatusCode($code, $text = null)
    {
        $this->statusCode = $code = (int) $code;
        if (null === $text) {
            $this->statusText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : 'unknown status';
            return $this;
        }
        if (false === $text) {
            $this->statusText = '';
            return $this;
        }
        $this->statusText = $text;
        return $this;
    }

    /**
     * Retrieves the status code for the current web response.
     * @return int Status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Add an array of headers to the response.
     *
     * @param  array  $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }
        return $this;
    }

    /**
     * Set a Cookie on the Response.
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function setCookie($key, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        $this->cookies[$key] = compact('key', 'value', 'expire', 'path', 'domain', 'secure', 'httponly');
        return $this;
    }

    /**
     * Add an array of Cookie to the response.
     *
     * @param  array  Cookie
     * @return $this
     */
    public function setCookies(array $cookie)
    {
        foreach ($cookie as $k => $value) {
            list($key, $value, $expire, $path , $domain , $secure, $httponly) = $value;
            $this->cookies[$key] = compact('key', 'value', 'expire', 'path', 'domain', 'secure', 'httponly');
        }
        return $this;
    }
    /**
     *
     * Example:
     *     return Response::create($body, 200)
     *         ->xxxx(300);
     *
     * @param mixed $content The response content, see setContent()
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return BaseResponse
     */
    public static function create($content = '', $status = 200, $headers = array())
    {
        return new static($content, $status, $headers);
    }



    public function getContent()
    {
        return $this->content;
    }

    public function getCookie()
    {
        return $this->cookies;
    }

    public function getHeader()
    {
        return $this->headers;
    }


    public function sendBy(SwooleHttpResponse $swooleHttpResponse)
    {
        $this->sendStatusBy($swooleHttpResponse);
        $this->sendHeadersBy($swooleHttpResponse);
        $this->sendCookiesBy($swooleHttpResponse);
        $this->sendContentBy($swooleHttpResponse);
    }

    /**
     * @param SwooleHttpResponse $swooleHttpResponse
     * @return $this
     */
    public function sendStatusBy(SwooleHttpResponse $swooleHttpResponse)
    {
        $swooleHttpResponse->status($this->getStatusCode());
    }
    /**
     * @param SwooleHttpResponse $swooleHttpResponse
     * @return $this
     */
    public function sendHeadersBy(SwooleHttpResponse $swooleHttpResponse)
    {
        $headers = $this->getHeader();
        if ($headers) {
            foreach ($headers as $name => $value) {
                $swooleHttpResponse->header($name, $value);
            }
        }
    }
    /**
     * @param SwooleHttpResponse $swooleHttpResponse
     * @return $this
     */
    public function sendCookiesBy(SwooleHttpResponse $swooleHttpResponse)
    {
        $cookies = $this->getCookie();
        if ($cookies) {
            foreach ($cookies as $k => $item) {
                list($key, $value, $expire, $path , $domain , $secure, $httponly) = $item;
                $swooleHttpResponse->cookie(
                    $key,
                    $value,
                    $expire,
                    $path,
                    $domain,
                    $secure,
                    $httponly
                );
            }
        }
    }



    /**
     * @param SwooleHttpResponse $swooleHttpResponse
     * @return $this
     */
    public function sendContentBy(SwooleHttpResponse $swooleHttpResponse)
    {
        $swooleHttpResponse->end($this->getContent());
        return $this;
    }
}
