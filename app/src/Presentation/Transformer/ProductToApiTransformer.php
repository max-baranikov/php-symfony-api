<?php

declare(strict_types=1);

namespace App\Presentation\Transformer;

use App\Application\ListProducts\DTO\PriceView;
use App\Application\ListProducts\DTO\ProductView;
use App\Domain\Product\Entity\Product;

class ProductToApiTransformer
{
    public function transform(Product $product): ProductView
    {
        $price = $product->getPrice();

        return new ProductView(
            $product->getSku(),
            $product->getName(),
            $product->getCategory(),
            new PriceView(
                $price->getOriginal(),
                $price->getFinal(),
                $price->getDiscountPercentage(),
                $price->getCurrency()
            )
        );
    }
}
