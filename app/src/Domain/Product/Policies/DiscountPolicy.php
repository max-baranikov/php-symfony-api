<?php

declare(strict_types=1);

namespace App\Domain\Product\Policies;

use App\Domain\Product\Entity\Product;

interface DiscountPolicy
{
    public function calculateDiscount(Product $product): int;
}
