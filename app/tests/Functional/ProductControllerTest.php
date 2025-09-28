<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Application\ListProducts\ListProductsHandler;
use App\Application\ListProducts\ListProductsQuery;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Policies\BootsCategoryPolicy;
use App\Domain\Product\Policies\SkuPolicy;
use App\Domain\Product\ValueObject\Price;
use App\Infrastructure\Http\Controller\ProductController;
use App\Infrastructure\Persistence\Doctrine\ProductRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(ProductController::class)]
#[UsesClass(ListProductsHandler::class)]
#[UsesClass(ListProductsQuery::class)]
#[UsesClass(Product::class)]
#[UsesClass(BootsCategoryPolicy::class)]
#[UsesClass(SkuPolicy::class)]
#[UsesClass(Price::class)]
#[UsesClass(ProductRepository::class)]

class ProductControllerTest extends WebTestCase
{
    public function testGetProducts(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('products', $response);
        $this->assertIsArray($response['products']);
        $this->assertLessThanOrEqual(5, count($response['products']));
    }

    public function testGetProductsWithCategoryFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products?category=boots');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('products', $response);

        foreach ($response['products'] as $product) {
            $this->assertEquals('boots', $product['category']);
        }
    }

    public function testGetProductsWithPriceFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products?priceLessThan=80000');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('products', $response);

        foreach ($response['products'] as $product) {
            $this->assertLessThanOrEqual(80000, $product['price']['original']);
        }
    }

    public function testProductStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('products', $response);

        if (! empty($response['products'])) {
            $product = $response['products'][0];
            $this->assertArrayHasKey('sku', $product);
            $this->assertArrayHasKey('name', $product);
            $this->assertArrayHasKey('category', $product);
            $this->assertArrayHasKey('price', $product);

            $price = $product['price'];
            $this->assertArrayHasKey('original', $price);
            $this->assertArrayHasKey('final', $price);
            $this->assertArrayHasKey('discount_percentage', $price);
            $this->assertArrayHasKey('currency', $price);
            $this->assertEquals('EUR', $price['currency']);
        }
    }
}
