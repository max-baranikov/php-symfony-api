<?php

declare(strict_types=1);

namespace App\Domain\Product\Policies;

use App\Domain\Product\Entity\Product;

class BootsCategoryPolicy implements DiscountPolicy
{
    public function calculateDiscount(Product $product): int
    {
        return $product->getCategory() === 'boots' ? 30 : 0;
    }
}
