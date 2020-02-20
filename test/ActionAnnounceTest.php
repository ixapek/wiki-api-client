<?php

namespace ixapek\WikiApiClient\Test;


use PHPUnit\Framework\TestCase;
use ixapek\WikiApiClient\Client;
use ixapek\WikiApiClient\Config;
use ixapek\WikiApiClient\ActionAnnounce;

class ActionAnnounceTest extends TestCase
{
    public function testGetUpcoming(){

//        $client = new Client(Config::API_ENDPOINT, Config::API_LOGIN, Config::API_PASSWORD, Config::API_COOKIE_FILE);
        $client = new Client('https://wiki.tass.ru/api.php', 'wiki_reader2', 'uuCpJa,ovf2~', 'cookie.txt');

        $actionAnnounce = new ActionAnnounce($client);

        $firstJan2019 = new \DateTime('2018-12-31 22:00:00');

        $upcoming = $actionAnnounce->getUpcoming(
            5,
            0,
            $firstJan2019->getTimestamp(),
            $firstJan2019->modify('+1 month')->getTimestamp()
        );

        $dt = new \DateTime();
        $dates = [];
        foreach ($upcoming->announce->result as $res){
            $dates[] = $dt->setTimestamp($res->date_start)->format('Y-m-d H:i:s');
        }

        $this->assertCount(3, $upcoming->announce->result);

        $this->assertEquals(150, $upcoming->announce->result[0]->namespace_id);
    }
}
