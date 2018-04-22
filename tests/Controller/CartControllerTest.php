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

    /**
     * @test
     */
    public function create_no_body()
    {
        $client = $this->makeClient();

        $client->request("POST", "/carts");

        $this->assertStatusCode(400, $client);
    }

    /**
     * @test
     */
    public function create_invalid_data()
    {
        $client = $this->makeClient();

        $content = <<<EOF
        {
            "currency": "ZZZ"
        }
EOF;

        $client->request("POST", "/carts", [], [], [], $content);

        $this->assertStatusCode(400, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();

        $this->assertJson($responseBody);

        $responseJson = json_decode($responseBody, true);
        $this->assertArrayHasKey("validationErrors", $responseJson);

        $validationErrors = $responseJson["validationErrors"];
        $this->assertArrayHasKey("currency", $validationErrors);
        $this->assertContains("not a valid currency", $validationErrors["currency"], true);
    }

    public function create_always_new_instance()
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

        $id1 = $cart['id'];

        $client->request("POST", "/carts", [], [], [], $content);

        $this->assertStatusCode(201, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $cart = json_decode($responseBody, true);

        $id2 = $cart['id'];

        $this->assertNotEquals($id1, $id2, "Expected different card ids");
    }
}