<?php

namespace Kernel\Components\WeChat\OfficialAccount\OAuth;

use Kernel\Components\WeChat\Kernel\Http\Response;

class Client extends BaseClient
{
    protected $component;

    protected $redirectUrl;

    protected $parameters = [];

    protected $scopes = ['snsapi_login'];

    protected $scopeSeparator = ',';

    protected $encodingType = PHP_QUERY_RFC1738;

    protected $withCountryCode = false;

    protected function initConfig()
    {
        $this->redirectUrl = $this->prepareCallbackUrl();
        $this->scopes = $this->app->getConfig('oauth.scopes', ['snsapi_userinfo']);
    }

    protected function prepareCallbackUrl()
    {
        $callback = $this->app->getConfig('oauth.callback', '');
        if (0 === stripos($callback, 'http')) {
            return $callback;
        }
        $baseUrl = $this->app->getConfig('oauth.base', '');

        return $baseUrl.'/'.ltrim($callback, '/');
    }

    public function redirect($redirectUrl = null)
    {
        $this->initConfig();

        $state = null;

        if (!is_null($redirectUrl)) {
            $this->redirectUrl = $redirectUrl;
        }

        return $this->getAuthUrl($state);
    }

    protected function getAuthUrl($state)
    {
        $path = 'oauth2/authorize';

        if (in_array('snsapi_login', $this->scopes)) {
            $path = 'qrconnect';
        }

        return $this->buildAuthUrlFromBase("https://open.weixin.qq.com/connect/{$path}", $state);
    }

    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);

        return $url.'?'.$query.'#wechat_redirect';
    }

    protected function getCodeFields($state = null)
    {
        if (isset($this->component)) {
            $this->parameters = ['component_appid' => $this->component->getAppId()];
        }

        return array_merge([
            'appid' => $this->app->getConfig('appid'),
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'state' => $state ?: md5(time()),
        ], $this->parameters);
    }

    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    public function getAccessToken($code)
    {
        $responseData = yield $this->getHttpClient()
            ->setMethod('GET')
            ->setHeaders([
                'Accept' => 'application/json',
            ])
            ->setQuery($this->getTokenFields($code))
            ->coroutineExecute($this->getTokenUrl());

        $response = new Response($responseData);

        return $this->parseAccessToken($response->getBody());
    }

    protected function getTokenFields($code)
    {
        return array_filter([
            'appid' => $this->app->getConfig('appid'),
            'secret' => $this->app->getConfig('appsecret'),
            'component_appid' => isset($this->component) ? $this->component->getAppId() : null,
            'component_access_token' => isset($this->component) ? $this->component->getToken() : null,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);
    }

    protected function getTokenUrl()
    {
        if (isset($this->component)) {
            return '/sns/oauth2/component/access_token';
        }

        return '/sns/oauth2/access_token';
    }

    protected function parseAccessToken($body)
    {
        if (!is_array($body)) {
            $body = json_decode($body, true);
        }

        if (empty($body['access_token'])) {
            throw new \Exception('Authorize Failed: '.json_encode($body, JSON_UNESCAPED_UNICODE), $body);
        }

        return new AccessToken($body);
    }

    public function user(AccessTokenInterface $token = null)
    {
        //TODO state

        $token = $token ?: yield $this->getAccessToken($this->getCode());

        $user = yield $this->getUserByToken($token);

        return $user;
    }

    protected function getCode()
    {
        return $this->app->getHttpInput()->get('code', '');
    }

    protected function getUserByToken(AccessTokenInterface $token)
    {
        $scopes = explode(',', $token->getAttribute('scope', ''));

        if (in_array('snsapi_base', $scopes)) {
            return $token->toArray();
        }

        if (empty($token['openid'])) {
            throw new \Exception('openid of AccessToken is required.');
        }

        $language = $this->withCountryCode ? null : (isset($this->parameters['lang']) ? $this->parameters['lang'] : 'zh_CN');

        $responseData = yield $this->getHttpClient()
            ->setMethod('GET')
            ->setQuery(array_filter([
                'access_token' => $token->getToken(),
                'openid' => $token['openid'],
                'lang' => $language,
            ]))
            ->coroutineExecute('/sns/userinfo');

        $response = new Response($responseData);

        return json_decode($response->getBody(), true);
    }
}
