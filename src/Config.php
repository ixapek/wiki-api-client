<?php


namespace ixapek\WikiApiClient;


use ixapek\WikiApiClient\Exception\ConfigException;

class Config
{

    public const AGENT_BOT = 'mwbot';      // Работа от имени бота (локальной учетной записи)
    public const AGENT_AD  = 'ad_user';    // Работа под учетной записью AD (корпоративная учетная запись)

    public const ENV_DEV  = 'development';       // Работа в окружении разработчика/недоверенной среды
    public const ENV_PROD = 'production';        // Работа в окружении прод среды с корректной настройкой

    /** @var string $apiEndpoint URL до api.php */
    protected $apiEndpoint;
    /** @var string $apiUser Пользователь api */
    protected $apiUser;
    /** @var string $apiPassword Пароль api */
    protected $apiPassword;

    /** @var string $agent Режим работы */
    protected $agent = Config::AGENT_BOT;
    /** @var string $env Окружение */
    protected $env = Config::ENV_DEV;
    /** @var string|null $cookieFile */
    protected $cookieFile;

    /**
     * Инициализация конфига из строки DSN и сопутствующих параметров
     *
     * @param string      $dsn
     * @param string|null $cookieFilePath
     * @param string      $agent
     * @return Config
     * @throws ConfigException
     */
    public static function fromDSN(string $dsn, ?string $cookieFilePath, string $agent = Config::AGENT_AD): Config
    {
        $url = parse_url($dsn);

        if( false === $url ){
            throw new ConfigException("DSN cannot be parsed");
        }

        $scheme = $url['scheme'] ?? 'https';
        $port = $url['port'] ?? 443;
        $path = $url['path'] ?? '/api.php';

        $env = $url['fragment'] ?? Config::ENV_DEV;

        if (true == empty($url['host'])) {
            throw new ConfigException("DSN host not defined");
        }

        if (true == empty($url['user'])) {
            throw new ConfigException("DSN user not defined");
        }

        if (true == empty($url['pass'])) {
            throw new ConfigException("DSN password not defined");
        }

        return (new static())
            ->setApiEndpoint("$scheme://{$url['host']}:$port$path")
            ->setApiUser($url['user'])
            ->setApiPassword($url['pass'])
            ->setAgent($agent)
            ->setEnv($env)
            ->setCookieFile($cookieFilePath);
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }

    /**
     * @param string $apiEndpoint
     * @return Config
     */
    public function setApiEndpoint(string $apiEndpoint): Config
    {
        $this->apiEndpoint = $apiEndpoint;
        return $this;
    }

    /**
     * @return string
     */
    public function getAgent(): string
    {
        return $this->agent;
    }

    /**
     * @param string $agent
     * @return Config
     * @throws ConfigException
     */
    public function setAgent(string $agent): Config
    {
        if( $agent !== Config::AGENT_AD && $agent !== Config::AGENT_BOT ){
            throw new ConfigException('Unknown agent');
        }
        $this->agent = $agent;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDevEnv(): bool
    {
        return $this->env === Config::ENV_DEV;
    }

    /**
     * @return bool
     */
    public function isProdEnv(): bool
    {
        return $this->env === Config::ENV_PROD;
    }

    /**
     * @param string $env
     * @return Config
     * @throws ConfigException
     */
    public function setEnv(string $env): Config
    {
        if( $env !== Config::ENV_PROD && $env !== Config::ENV_DEV ){
            throw new ConfigException('Unknown environment');
        }
        $this->env = $env;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCookieFile(): ?string
    {
        return $this->cookieFile;
    }

    /**
     * @param string|null $cookieFile
     * @return Config
     * @throws ConfigException
     */
    public function setCookieFile(?string $cookieFile): Config
    {
        if(null !== $cookieFile){
            if(true === file_exists($cookieFile)){
                if( false === is_writable($cookieFile) ){
                    throw new ConfigException("$cookieFile isn't writeable");
                }
            } else {
                $cookieFileDir = dirname($cookieFile);
                if( false === is_writable($cookieFileDir) ){
                    throw new ConfigException("$cookieFile not exist and $cookieFileDir isn't writeable");
                }
            }
        }

        $this->cookieFile = $cookieFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiUser(): string
    {
        return $this->apiUser;
    }

    /**
     * @param string $apiUser
     * @return Config
     */
    public function setApiUser(string $apiUser): Config
    {
        $this->apiUser = $apiUser;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiPassword(): string
    {
        return $this->apiPassword;
    }

    /**
     * @param string $apiPassword
     * @return Config
     */
    public function setApiPassword(string $apiPassword): Config
    {
        $this->apiPassword = $apiPassword;
        return $this;
    }
}