<?php


namespace ixapek\WikiApiClient;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use ixapek\WikiApiClient\Exception\ConfigException;


class ApiClient
{
    /** @var Config $config */
    protected $config;

    /** @var Client $guzzleClient */
    protected $guzzleClient;

    /**
     * @param string      $dsn
     * @param string|null $cookieFilePath
     * @param string      $agent
     * @return ApiClient
     * @throws ConfigException
     */
    public static function init(string $dsn, ?string $cookieFilePath, string $agent = Config::AGENT_AD): ApiClient
    {
        return new static(Config::fromDSN($dsn, $cookieFilePath, $agent));
    }

    public function __construct(Config $config)
    {
        $guzzleConfig = [
            'base_uri'                  => $config->getApiEndpoint(),
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::HEADERS     => [
                'User-Agent' => $config->getAgent(),
            ],
        ];

        if (true === $config->isDevEnv()) {
            $guzzleConfig[RequestOptions::VERIFY] = false;
        }

        $guzzleConfig[RequestOptions::COOKIES] = (null === $config->getCookieFile()) ?
            new CookieJar() :
            new FileCookieJar($config->getCookieFile(), true);


        $this->guzzleClient = new Client($guzzleConfig);
    }

    public function request()
    {

    }
}