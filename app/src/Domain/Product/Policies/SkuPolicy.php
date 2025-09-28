<?php

declare(strict_types=1);

namespace App\Domain\Product\Policies;

use App\Domain\Product\Entity\Product;

class SkuPolicy implements DiscountPolicy
{
    public function calculateDiscount(Product $product): int
    {
        return $product->getSku() === '000003' ? 15 : 0;
    }
}
