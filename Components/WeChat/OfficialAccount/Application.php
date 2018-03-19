<?php

namespace Kernel\Components\WeChat\OfficialAccount;

use Kernel\Components\WeChat\OfficialAccount\OAuth\Client as OAuth;
use Kernel\CoreBase\HttpInput;
use Kernel\CoreBase\Model;

/**
 * Class Application
 *
 * @package \Kernel\Components\WeChat\OfficialAccount
 */
class Application extends Model
{
    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var HttpInput
     */
    private $httpInput;

    /**
     * @var OAuth
     */
    public $oauth;

    public function initialization(&$context)
    {
        parent::initialization($context);

        $this->oauth = $this->loader->model(OAuth::class, $this);
    }

    /**
     * @param $key
     * @param string $default
     * @return mixed
     */
    public function getConfig($key, $default = '')
    {
        $array = $this->configs;

        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * @param array $configs
     * @return $this
     */
    public function setConfigs(array $configs)
    {
        $this->configs = $configs;
        return $this;
    }

    /**
     * @return HttpInput
     */
    public function getHttpInput()
    {
        return $this->httpInput;
    }

    /**
     * @param HttpInput $httpInput
     * @return $this
     */
    public function setHttpInput(HttpInput $httpInput)
    {
        $this->httpInput = $httpInput;
        return $this;
    }
}
