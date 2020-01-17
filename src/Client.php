<?php


namespace ixapek\WikiApiClient;


use Exception;

/**
 * Class Client
 *
 * @package ixapek\WikiApiClient
 */
class Client
{
    const MODE_BOT     = 'mwbot';
    const MODE_AD      = 'ad_user';
    const MODE_DEFAULT = self::MODE_AD;

    /** @var string $login */
    protected $login;
    /** @var string $password */
    protected $password;

    /** @var string $loginToken */
    protected $loginToken;
    /** @var resource $curl */
    protected $curl;

    /** @var array $options */
    protected $options = [
        CURLOPT_COOKIESESSION  => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'User-Agent: mwbot',
        ],
    ];

    /**
     * Client constructor.
     *
     * @param string $endpoint
     * @param string $login
     * @param string $password
     * @param string $cookieFile
     * @param string $mode
     * @throws Exception
     */
    public function __construct($endpoint, $login, $password, $cookieFile, $mode = self::MODE_DEFAULT)
    {
        $endpoint = $this->checkEndpoint($endpoint);

        $this->login = $login;
        $this->password = $password;

        $this->options[CURLOPT_URL] = $endpoint;
        $this->options[CURLOPT_COOKIEFILE] = $this->options[CURLOPT_COOKIEJAR] = $cookieFile;

        if(false === in_array($mode, [self::MODE_BOT, self::MODE_AD]) ){
            throw new Exception('Unsuported mode');
        }

        $this->options[CURLOPT_HTTPHEADER] = ["User-Agent: $mode"];
    }

    /**
     * Check MW API endpoint correct and accessibility
     *
     * @param string $endpoint
     * @return string
     * @throws Exception
     */
    protected function checkEndpoint(string $endpoint):string {
        $endpointComponents = parse_url($endpoint);

        $scheme = $endpointComponents['scheme'] ?? null;
        $host = $endpointComponents['host'] ?? null;
        $path = $endpointComponents['path'] ?? '';

        if( false === strpos($path, 'api.php')  ){
            throw new Exception("Incorrect endpoint: MW API endpoint must contains '/api.php' in path");
        }

        if( null === $host ){
            throw new Exception("Incorrect endpoint: host not presented");
        }

        if( null === $scheme ){
            $scheme = 'https';
        }

        switch ($scheme) {
            case 'http':
                $port = 80;
                $transport = 'udp';
                break;
            case 'https':
                $port = 443;
                $transport = 'ssl';
                break;
            default:
                throw new Exception("Incorrect endpoint: scheme not presented");
        }

        if (@gethostbyname($host) === $host) {
            throw new Exception("MW API host is unresolved: $host");
        }

        $sockCheck = @fsockopen("$transport://$host", $port, $errno, $error, 10);
        if (false === $sockCheck) {
            throw new Exception("MW API host ($scheme://$host:$port) is unavailable: [$errno] $error");
        }
        fclose($sockCheck);

        return "$scheme://$host$path";
    }

    /**
     * @return false|resource
     */
    public function getCurl()
    {
        if (null === $this->curl) {
            $this->curl = curl_init();
        }

        return $this->curl;
    }

    /**
     * @param array $postParams
     * @return mixed
     * @throws Exception
     */
    public function request(array $postParams)
    {
        $postParams['format'] = 'json';

        $this->checkAuthentication($postParams);

        $curlOptions = $this->getOptions([
            CURLOPT_POSTFIELDS => http_build_query($postParams),
        ]);

        curl_setopt_array(
            $this->getCurl(),
            $curlOptions
        );

        $curlExec = curl_exec($this->getCurl());

        if (false === $curlExec) {
            throw new Exception(curl_error($this->getCurl()));
        }

        $data = json_decode($curlExec);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error parsing JSON response: " . json_last_error_msg() . " - $curlExec");
        }

        return $data;
    }

    /**
     * @param array $postParams
     * @throws Exception
     */
    public function checkAuthentication(array $postParams)
    {
        if ($this->loginToken === null && $postParams['action'] !== 'login' && $postParams['type'] !== 'login') {
            $this->apiLogin();
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function apiLogin()
    {
        $loginResultStatus = null;
        if (true === $this->loadLoginToken()) {
            $loginResult = $this->request([
                'action'     => 'login',
                'lgname'     => $this->login,
                'lgpassword' => $this->password,
                'lgtoken'    => $this->loginToken,
            ]);

            if (true === isset($loginResult->login->result)) {
                $loginResultStatus = $loginResult->login->result;
            }
        }

        return ($loginResultStatus === 'Success');
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function loadLoginToken()
    {
        if ($this->loginToken === null) {
            $tokenResult = $this->request([
                'action' => 'query',
                'meta'   => 'tokens',
                'type'   => 'login',
            ]);

            if (true === isset($tokenResult->query->tokens->logintoken)) {
                $this->loginToken = $tokenResult->query->tokens->logintoken;
            }
        }

        return $this->loginToken !== null;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getOptions(array $options = [])
    {
        return ($this->options + $options);
    }
}