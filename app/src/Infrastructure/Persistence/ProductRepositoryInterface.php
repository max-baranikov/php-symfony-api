<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Product\Entity\Product;

interface ProductRepositoryInterface
{
    /**
     * @return Product[]
     */
    public function findByFilters(?string $category, ?int $priceLessThan): array;
}
