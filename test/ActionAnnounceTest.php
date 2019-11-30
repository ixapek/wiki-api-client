<?php

namespace ixapek\WikiApiClient\Test;


use PHPUnit\Framework\TestCase;
use ixapek\WikiApiClient\Client;
use ixapek\WikiApiClient\Config;
use ixapek\WikiApiClient\ActionAnnounce;

class ActionAnnounceTest extends TestCase
{
    public function testGetUpcoming(){
        $actionAnnounce = new ActionAnnounce(
            new Client(Config::API_ENDPOINT, Config::API_LOGIN, Config::API_PASSWORD, Config::API_COOKIE_FILE)
        );

        $upcoming = $actionAnnounce->getUpcoming(3);

        $this->assertCount(3, $upcoming->announce->result);

        $this->assertEquals(150, $upcoming->announce->result[0]->namespace_id);
    }
}
