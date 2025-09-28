<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObject;

class Price
{
    public function __construct(
        private readonly int $original,
        private readonly int $final,
        private readonly ?string $discountPercentage,
        private readonly string $currency = 'EUR'
    ) {
    }

    public function getOriginal(): int
    {
        return $this->original;
    }

    public function getFinal(): int
    {
        return $this->final;
    }

    public function getDiscountPercentage(): ?string
    {
        return $this->discountPercentage;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function applyDiscount(int $discountPercentage): Price
    {
        $discountAmount = (int) round($this->original * $discountPercentage / 100);
        $finalPrice = $this->original - $discountAmount;

        return new self(
            $this->original,
            $finalPrice,
            $discountPercentage . '%',
            $this->currency
        );
    }

    public static function create(int $amount): Price
    {
        return new self($amount, $amount, null);
    }
}
