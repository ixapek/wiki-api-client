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
    public function getUpcoming($limit = 5, $offset = 0)
    {
        return $this->client->request([
            'action'     => 'announce',
            'type'       => 'upcoming',
            'method'     => 'get',
            'key_format' => 'number',
            'from'       => (new DateTime())->getTimestamp(),
            'limit'      => $limit,
            'offset'     => $offset,
        ]);
    }
}