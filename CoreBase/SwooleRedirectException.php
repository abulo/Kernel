<?php
/**
 * Created by PhpStorm.
 * User: abulo
 * Date: 16-7-15
 * Time: 上午11:35
 */
namespace Kernel\CoreBase;

use Exception;

/**
 * 重定向
 * Class SwooleRedirectException
 * @package Kernel\CoreBase
 */
class SwooleRedirectException extends \Exception
{
    public function __construct($location, $code, Exception $previous = null)
    {
        parent::__construct($location, $code, $previous);
    }
}
