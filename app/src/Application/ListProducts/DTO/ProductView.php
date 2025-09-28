<?php

declare(strict_types=1);

namespace App\Application\ListProducts\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductView',
    type: 'object',
    properties: [
        new OA\Property(property: 'sku', type: 'string', example: '000001'),
        new OA\Property(property: 'name', type: 'string', example: 'Test Product'),
        new OA\Property(property: 'category', type: 'string', example: 'boots'),
        new OA\Property(
            property: 'price',
            type: 'object',
            properties: [
                new OA\Property(property: 'original', type: 'integer', example: 89000),
                new OA\Property(property: 'final', type: 'integer', example: 62300),
                new OA\Property(property: 'discount_percentage', type: 'string', nullable: true, example: '30%'),
                new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
            ]
        ),
    ]
)]
class ProductView
{
    public function __construct(
        public readonly string $sku,
        public readonly string $name,
        public readonly string $category,
        public readonly PriceView $price
    ) {
    }
}
