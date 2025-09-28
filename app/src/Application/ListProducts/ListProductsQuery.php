<?php

declare(strict_types=1);

namespace App\Application\ListProducts;

class ListProductsQuery
{
    public function __construct(
        private readonly ?string $category = null,
        private readonly ?int $priceLessThan = null
    ) {
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getPriceLessThan(): ?int
    {
        return $this->priceLessThan;
    }
}
