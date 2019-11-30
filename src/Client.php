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
    const MODE_BOT     = 'bot';
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
        $this->login = $login;
        $this->password = $password;

        $this->options[CURLOPT_URL] = $endpoint;
        $this->options[CURLOPT_COOKIEFILE] = $this->options[CURLOPT_COOKIEJAR] = $cookieFile;

        switch ($mode) {
            case self::MODE_BOT:
                $this->options[CURLOPT_HTTPHEADER] = ['User-Agent: mwbot'];
                break;
            case self::MODE_AD:
                $this->options[CURLOPT_HTTPHEADER] = ['User-Agent: mwaduser'];
                break;
            default:
                throw new Exception('Unsuported mode');
        }
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
            CURLOPT_POSTFIELDS => $postParams,
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
            throw new Exception(json_last_error_msg());
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