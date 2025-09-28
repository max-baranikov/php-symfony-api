<?php

declare(strict_types=1);

namespace App\Application\ListProducts\DTO;

class PriceView
{
    public function __construct(
        public readonly int $original,
        public readonly int $final,
        public readonly ?string $discountPercentage,
        public readonly string $currency = 'EUR'
    ) {
    }
}
