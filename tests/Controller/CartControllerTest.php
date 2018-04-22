<?php

namespace App\Tests\Controller;

use App\Tests\Fixtures\BaseWebTest;

class CartControllerTest extends BaseWebTest
{
    //tests for cart creation endpoint

    /**
     * @test
     */
    public function create_successful()
    {
        $content = <<<EOF
        {
            "currency": "USD"
        }
EOF;

        $client = $this->makeClient();

        $client->request("POST", "/carts", [], [], [], $content);

        $this->assertStatusCode(201, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $cart = json_decode($responseBody, true);

        $this->assertArrayHasKey("id", $cart);
        $this->assertNotEmpty($cart['id']);
    }
}