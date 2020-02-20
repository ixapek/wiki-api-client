<?php


namespace ixapek\WikiApiClient;


use DateTime;
use Exception;

class ActionAnnounce
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @throws Exception
     */
    public function getUpcoming($limit = 5, $offset = 0, $tsFrom = null, $tsTo = null)
    {
        echo http_build_query([
            'action'     => 'announce',
            'type'       => '*',
            'method'     => 'get',
            'key_format' => 'number',
            'from'       => $tsFrom,
            'to'         => $tsTo,
            'limit'      => $limit,
            'offset'     => $offset,
        ]);

        $ts = $ts ?? (new DateTime())->getTimestamp();
        return $this->client->request([
            'action'     => 'announce',
            'type'       => '*',
            'method'     => 'get',
            'key_format' => 'number',
            'from'       => $tsFrom,
            'to'         => $tsTo,
            'limit'      => $limit,
            'offset'     => $offset,
        ]);
    }
}