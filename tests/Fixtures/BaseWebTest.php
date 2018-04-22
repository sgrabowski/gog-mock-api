<?php

namespace App\Tests\Fixtures;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class BaseWebTest extends WebTestCase
{
    protected function makeClient($authentication = false, array $params = []): Client
    {
        $client = parent::makeClient($authentication, $params);
        $client->setServerParameters([
            "CONTENT_TYPE" => "application/json",
            "ACCEPT" => "application/json"
        ]);

        return $client;
    }
}