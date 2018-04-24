<?php

namespace App\Tests\Controller;

use App\DataFixtures\ProductFixtures;
use App\Tests\Fixtures\BaseWebTest;
use Symfony\Bundle\FrameworkBundle\Client;

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

        $this->assertArrayHasKey("currency", $cart);
        $this->assertEquals("USD", $cart['currency']);
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

    //tests for adding products

    /**
     * @test
     */
    public function update_successful()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();
        $cartId = $this->createCart($client);
        $productId = $references->getReference("fallout")->getId();

        $url = "/carts/" . $cartId . "/products/" . $productId . "?quantity=2";
        $client->request("PUT", $url);

        $this->assertStatusCode(200, $client);

        //make sure this PUT operation is idempotent
        $client->request("PUT", $url);

        $this->assertStatusCode(200, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $cart = json_decode($responseBody, true);

        $this->assertArrayHasKey("id", $cart);
        $this->assertEquals($cartId, $cart['id']);

        $this->assertArrayHasKey("currency", $cart);
        $this->assertEquals("USD", $cart['currency']);

        $this->assertArrayHasKey("total", $cart);
        $this->assertEquals("3.98", $cart['total']);

        $this->assertArrayHasKey("products", $cart);
        $this->assertCount(1, $cart['products']);

        $cartProduct = $cart['products'][0];
        $this->assertArrayHasKey("product", $cartProduct);
        $this->assertArrayHasKey("quantity", $cartProduct);
        $this->assertEquals(2, $cartProduct['quantity']);

        $product = $cartProduct['product'];
        $this->assertArrayHasKey("id", $product);
        $this->assertArrayHasKey("title", $product);
        $this->assertArrayHasKey("prices", $product);
        $this->assertEquals("Fallout", $product['title']);
    }

    protected function createCart(Client $client, $currency = "USD")
    {
        $client->request("POST", "/carts", [], [], [], '{"currency":"' . $currency . '"}');
        return json_decode($client->getResponse()->getContent(), true)['id'];
    }

    /**
     * @test
     */
    public function update_invalid_product()
    {
        $client = $this->makeClient();
        $cartId = $this->createCart($client);

        $url = "/carts/" . $cartId . "/products/666";
        $client->request("PUT", $url);

        $this->assertStatusCode(400, $client);
        //@TODO: check for messages
    }

    /**
     * @test
     */
    public function update_invalid_cart()
    {
        $this->loadFixtures([]);
        $client = $this->makeClient();

        $url = "/carts/666/products/666";
        $client->request("PUT", $url);

        $this->assertStatusCode(404, $client);
        //@TODO: check for messages
    }

    /**
     * @test
     */
    public function update_quantity_over_max()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();
        $cartId = $this->createCart($client);
        $productId = $references->getReference("fallout")->getId();

        $url = "/carts/" . $cartId . "/products/" . $productId . "?quantity=11";
        $client->request("PUT", $url);

        $this->assertStatusCode(400, $client);
        //@TODO: check for messages
    }

    /**
     * @test
     */
    public function update_cart_filled()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();
        $cartId = $this->createCart($client);
        $productId1 = $references->getReference("fallout")->getId();
        $productId2 = $references->getReference("icewinddale")->getId();
        $productId3 = $references->getReference("bloodborne")->getId();
        $productId4 = $references->getReference("dontstarve")->getId();

        $url = "/carts/" . $cartId . "/products/" . $productId1 . "?quantity=10";
        $client->request("PUT", $url);

        $this->assertStatusCode(200, $client);

        $url = "/carts/" . $cartId . "/products/" . $productId2 . "?quantity=10";
        $client->request("PUT", $url);

        $this->assertStatusCode(200, $client);

        $url = "/carts/" . $cartId . "/products/" . $productId3 . "?quantity=10";
        $client->request("PUT", $url);

        $this->assertStatusCode(200, $client);

        $url = "/carts/" . $cartId . "/products/" . $productId4 . "?quantity=10";
        $client->request("PUT", $url);

        $this->assertStatusCode(400, $client);
        //@TODO: check for messages
    }

    //cart info endpoint test

    /**
     * @test
     */
    public function get_cart()
    {
        $references = $this->loadFixtures([
            ProductFixtures::class
        ])->getReferenceRepository();

        $client = $this->makeClient();
        $cartId = $this->createCart($client);
        $productId = $references->getReference("fallout")->getId();

        $url = "/carts/" . $cartId . "/products/" . $productId . "?quantity=2";
        $client->request("PUT", $url);

        $this->assertStatusCode(200, $client);

        $client->request("GET", "/carts/".$cartId);
        $this->assertStatusCode(200, $client);

        $responseBody = $client->getResponse()->getContent();
        $this->assertJson($responseBody);
        $cart = json_decode($responseBody, true);

        $this->assertArrayHasKey("id", $cart);
        $this->assertEquals($cartId, $cart['id']);

        $this->assertArrayHasKey("currency", $cart);
        $this->assertEquals("USD", $cart['currency']);

        $this->assertArrayHasKey("total", $cart);
        $this->assertEquals("3.98", $cart['total']);

        $this->assertArrayHasKey("products", $cart);
        $this->assertCount(1, $cart['products']);

        $cartProduct = $cart['products'][0];
        $this->assertArrayHasKey("product", $cartProduct);
        $this->assertArrayHasKey("quantity", $cartProduct);
        $this->assertEquals(2, $cartProduct['quantity']);

        $product = $cartProduct['product'];
        $this->assertArrayHasKey("id", $product);
        $this->assertArrayHasKey("title", $product);
        $this->assertArrayHasKey("prices", $product);
        $this->assertEquals("Fallout", $product['title']);
    }
}