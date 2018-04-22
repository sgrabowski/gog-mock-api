<?php

namespace Test\App\Controller;

use App\DataFixtures\ProductFixtures;
use App\Tests\Fixtures\BaseWebTest;

class ProductControllerTest extends BaseWebTest
{
    //tests for product creation endpoint

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

    //tests for product and price update endpoints

    /**
     * @test
     */
    public function update_successful()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();

        $content = <<<EOF
        {
            "title": "Fallout: New Vegas"
        }
EOF;

        $falloutId = $references->getReference("fallout")->getId();
        $client->request("PATCH", "/products/" . $falloutId, [], [], [], $content);

        $this->assertStatusCode(200, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $product = json_decode($responseBody, true);

        $this->assertArrayHasKey("title", $product);

        $this->assertEquals("Fallout: New Vegas", $product['title']);
    }

    /**
     * @test
     */
    public function update_name_collision()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();

        $content = <<<EOF
        {
            "title": "Bloodborne"
        }
EOF;

        $falloutId = $references->getReference("fallout")->getId();
        $client->request("PATCH", "/products/" . $falloutId, [], [], [], $content);

        $this->assertStatusCode(400, $client);
        $response = $client->getResponse();
        $responseBody = $response->getContent();

        $this->assertJson($responseBody);

        $responseJson = json_decode($responseBody, true);
        $this->assertArrayHasKey("validationErrors", $responseJson);

        $validationErrors = $responseJson["validationErrors"];
        $this->assertArrayHasKey("title", $validationErrors);
        $this->assertContains('Product "Bloodborne" already exists', $validationErrors["title"], true);
    }

    /**
     * @test
     */
    public function update_empty_or_null_doesnt_do_anything()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();

        $content = <<<EOF
        {
            "title": ""
        }
EOF;

        $falloutId = $references->getReference("fallout")->getId();
        $client->request("PATCH", "/products/" . $falloutId, [], [], [], $content);

        $this->assertStatusCode(200, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $product = json_decode($responseBody, true);

        $this->assertArrayHasKey("title", $product);

        $this->assertEquals("Fallout", $product['title']);

        //test null the same way

        $content = <<<EOF
        {
            "title": null
        }
EOF;

        $falloutId = $references->getReference("fallout")->getId();
        $client->request("PATCH", "/products/" . $falloutId, [], [], [], $content);

        $this->assertStatusCode(200, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $product = json_decode($responseBody, true);

        $this->assertArrayHasKey("title", $product);

        $this->assertEquals("Fallout", $product['title']);
    }

    /**
     * @test
     */
    public function replace()
    {
        /**
         * @test
         */
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();

        $content = <<<EOF
        {
            "title": "Fallout: New Vegas",
            "prices": [
                {
                    "currency": "PLN",
                    "amount": "129.99"
                }
            ]
        }
EOF;

        $falloutId = $references->getReference("fallout")->getId();
        $client->request("PUT", "/products/" . $falloutId, [], [], [], $content);

        $this->assertStatusCode(200, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $product = json_decode($responseBody, true);

        $this->assertArrayHasKey("title", $product);

        $this->assertEquals("Fallout: New Vegas", $product['title']);
        $this->assertArrayHasKey("prices", $product);

        $prices = $product['prices'];
        $this->assertCount(1, $prices);
        $this->assertArrayHasKey("currency", $prices[0]);
        $this->assertArrayHasKey("amount", $prices[0]);
        $this->assertEquals("PLN", $prices[0]['currency']);
        $this->assertEquals("129.99", $prices[0]['amount']);
    }

    /**
     * @test
     */
    public function update_existing_price()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();

        $content = <<<EOF
{
    "currency": "USD",
    "amount": "999.99"
}
EOF;

        $falloutId = $references->getReference("fallout")->getId();
        $client->request("POST", "/products/" . $falloutId . "/prices", [], [], [], $content);

        $this->assertStatusCode(200, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $product = json_decode($responseBody, true);

        $this->assertArrayHasKey("prices", $product);
        $prices = $product['prices'];
        $this->assertCount(1, $prices);
        $this->assertArrayHasKey("currency", $prices[0]);
        $this->assertArrayHasKey("amount", $prices[0]);
        $this->assertEquals("USD", $prices[0]['currency']);
        $this->assertEquals("999.99", $prices[0]['amount']);
    }

    public function update_price_create_new()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();

        $content = <<<EOF
{
    "currency": "PLN",
    "amount": "999.99"
}
EOF;

        $falloutId = $references->getReference("fallout")->getId();
        $client->request("POST", "/products/" . $falloutId . "/prices", [], [], [], $content);

        $this->assertStatusCode(200, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $product = json_decode($responseBody, true);

        $this->assertArrayHasKey("prices", $product);
        $prices = $product['prices'];
        $this->assertCount(2, $prices);
    }
}
