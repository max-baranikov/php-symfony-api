<?php

declare(strict_types=1);

namespace App\Application\ListProducts;

use App\Application\ListProducts\DTO\ProductView;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Policies\DiscountPolicy;
use App\Infrastructure\Persistence\ProductRepositoryInterface;
use App\Presentation\Transformer\ProductToApiTransformer;

class ListProductsHandler
{
    /**
     * @param DiscountPolicy[] $discountPolicies
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductToApiTransformer $productToApiTransformer,
        private readonly iterable $discountPolicies
    ) {
    }

    /**
     * @return array<int, ProductView>
    */
    public function handle(ListProductsQuery $query): array
    {
        $products = $this->productRepository->findByFilters(
            $query->getCategory(),
            $query->getPriceLessThan()
        );

        $productViews = [];
        foreach ($products as $product) {
            $discountedProduct = $this->applyDiscounts($product);
            $productViews[] = $this->productToApiTransformer->transform($discountedProduct);
        }

        return $productViews;
    }

    private function applyDiscounts(Product $product): Product
    {
        $maxDiscount = 0;

        foreach ($this->discountPolicies as $policy) {
            $discount = $policy->calculateDiscount($product);
            $maxDiscount = max($maxDiscount, $discount);
        }

        if ($maxDiscount > 0) {
            return $product->applyDiscount(new class ($maxDiscount) implements DiscountPolicy {
                public function __construct(private readonly int $discount)
                {
                }
                public function calculateDiscount(Product $product): int
                {
                    return $this->discount;
                }
            });
        }

        return $product;
    }
}
