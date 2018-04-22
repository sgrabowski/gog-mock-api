<?php

namespace Test\App\Controller;

use App\DataFixtures\ProductFixtures;
use App\Tests\Fixtures\BaseWebTest;

class ProductControllerTest extends BaseWebTest
{
    /**
     * @test
     */
    public function create_successful()
    {
        $this->loadFixtures([
            ProductFixtures::class
        ]);

        $content = <<<EOF
        {
            "title": "Monster Hunter World",
            "prices": [
                {
                    "currency": "USD",
                    "amount": 9.99
                }
            ] 
        }
EOF;

        $client = $this->makeClient();

        $client->request("POST", "/products", [], [], [], $content);

        $this->assertStatusCode(201, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $product = json_decode($responseBody, true);

        $this->assertArrayHasKey("title", $product);
        $this->assertArrayHasKey("prices", $product);

        $this->assertEquals("Monster Hunter World", $product['title']);
        $prices = $product['prices'];

        $this->assertCount(1, $prices);
        $this->assertArrayHasKey("currency", $prices[0]);
        $this->assertArrayHasKey("amount", $prices[0]);
        $this->assertEquals("USD", $prices[0]['currency']);
        $this->assertEquals("9.99", $prices[0]['amount']);
    }

    /**
     * @test
     */
    public function create_no_body()
    {
        $client = $this->makeClient();

        $client->request("POST", "/products");

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
            "title": "",
            "prices": [
                {
                    "currency": "",
                    "amount": ""
                }
            ] 
        }
EOF;

        $client->request("POST", "/products", [], [], [], $content);

        $this->assertStatusCode(400, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();

        $this->assertJson($responseBody);

        $responseJson = json_decode($responseBody, true);
        $this->assertArrayHasKey("validationErrors", $responseJson);

        $validationErrors = $responseJson["validationErrors"];
        $this->assertArrayHasKey("title", $validationErrors);
        $this->assertArrayHasKey("prices[0].currency", $validationErrors);
        $this->assertArrayHasKey("prices[0].amount", $validationErrors);
        $this->assertContains("required", $validationErrors["title"], true);
        $this->assertContains("required", $validationErrors["prices[0].currency"], true);
        $this->assertContains("required", $validationErrors["prices[0].amount"], true);
    }

    /**
     * @test
     */
    public function create_without_prices()
    {
        $this->loadFixtures([]);
        $client = $this->makeClient();

        $content = <<<EOF
        {
            "title": "Fallout"
        }
EOF;

        $client->request("POST", "/products", [], [], [], $content);

        $this->assertStatusCode(201, $client);
    }

    /**
     * @test
     */
    public function create_name_collision()
    {
        $this->loadFixtures([
            ProductFixtures::class
        ]);

        $client = $this->makeClient();

        $content = <<<EOF
        {
            "title": "Fallout"
        }
EOF;

        $client->request("POST", "/products", [], [], [], $content);

        $this->assertStatusCode(400, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();

        $this->assertJson($responseBody);

        $responseJson = json_decode($responseBody, true);
        $this->assertArrayHasKey("validationErrors", $responseJson);

        $validationErrors = $responseJson["validationErrors"];
        $this->assertArrayHasKey("title", $validationErrors);
        $this->assertContains('Product "Fallout" already exists', $validationErrors["title"], true);
    }
}
