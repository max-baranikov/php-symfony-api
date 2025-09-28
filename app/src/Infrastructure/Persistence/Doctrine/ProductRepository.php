<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\Price;
use App\Infrastructure\Persistence\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return array<int, Product>
     */
    public function findByFilters(?string $category, ?int $priceLessThan): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(ProductEntity::class, 'p');

        if ($category !== null) {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $category);
        }

        if ($priceLessThan !== null) {
            $qb->andWhere('p.priceCents <= :priceLessThan')
               ->setParameter('priceLessThan', $priceLessThan);
        }

        $qb->orderBy('p.sku', 'ASC')
           ->setMaxResults(5);

        /** @var array<int, ProductEntity> $entities */
        $entities = $qb->getQuery()->getResult();

        return array_map(
            fn (ProductEntity $entity) => new Product(
                $entity->getSku(),
                $entity->getName(),
                $entity->getCategory(),
                Price::create($entity->getPriceCents())
            ),
            $entities
        );
    }
}
