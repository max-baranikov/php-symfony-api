<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Product\Policies\DiscountPolicy;
use App\Domain\Product\ValueObject\Price;

class Product
{
    public function __construct(
        private readonly string $sku,
        private readonly string $name,
        private readonly string $category,
        private readonly Price $price
    ) {
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function applyDiscount(DiscountPolicy $discountPolicy): Product
    {
        $discountPercentage = $discountPolicy->calculateDiscount($this);

        if ($discountPercentage === 0) {
            return $this;
        }

        $discountedPrice = $this->price->applyDiscount($discountPercentage);

        return new self(
            $this->sku,
            $this->name,
            $this->category,
            $discountedPrice
        );
    }
}
