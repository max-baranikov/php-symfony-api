<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Product;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Policies\BootsCategoryPolicy;
use App\Domain\Product\Policies\SkuPolicy;
use App\Domain\Product\ValueObject\Price;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Product::class)]
#[UsesClass(Price::class)]                // код, который допустимо/ожидаемо используется
#[UsesClass(BootsCategoryPolicy::class)]
#[UsesClass(SkuPolicy::class)]
class ProductTest extends TestCase
{
    public function testProductCreation(): void
    {
        $price = Price::create(89000);
        $product = new Product('000001', 'Test Product', 'boots', $price);

        $this->assertEquals('000001', $product->getSku());
        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals('boots', $product->getCategory());
        $this->assertEquals($price, $product->getPrice());
    }

    public function testBootsDiscount(): void
    {
        $price = Price::create(89000);
        $product = new Product('000001', 'Test Product', 'boots', $price);
        $policy = new BootsCategoryPolicy();

        $discountedProduct = $product->applyDiscount($policy);
        $discountedPrice = $discountedProduct->getPrice();

        $this->assertEquals(89000, $discountedPrice->getOriginal());
        $this->assertEquals(62300, $discountedPrice->getFinal());
        $this->assertEquals('30%', $discountedPrice->getDiscountPercentage());
    }

    public function testSkuDiscount(): void
    {
        $price = Price::create(71000);
        $product = new Product('000003', 'Test Product', 'boots', $price);
        $policy = new SkuPolicy();

        $discountedProduct = $product->applyDiscount($policy);
        $discountedPrice = $discountedProduct->getPrice();

        $this->assertEquals(71000, $discountedPrice->getOriginal());
        $this->assertEquals(60350, $discountedPrice->getFinal());
        $this->assertEquals('15%', $discountedPrice->getDiscountPercentage());
    }

    public function testNoDiscount(): void
    {
        $price = Price::create(59000);
        $product = new Product('000005', 'Test Product', 'sneakers', $price);
        $policy = new BootsCategoryPolicy();

        $discountedProduct = $product->applyDiscount($policy);
        $discountedPrice = $discountedProduct->getPrice();

        $this->assertEquals(59000, $discountedPrice->getOriginal());
        $this->assertEquals(59000, $discountedPrice->getFinal());
        $this->assertNull($discountedPrice->getDiscountPercentage());
    }
}
