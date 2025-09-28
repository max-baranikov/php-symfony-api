<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class ProductEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $sku;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $category;

    #[ORM\Column(type: 'integer')]
    private int $priceCents;

    public function __construct(
        string $sku,
        string $name,
        string $category,
        int $priceCents
    ) {
        $this->sku = $sku;
        $this->name = $name;
        $this->category = $category;
        $this->priceCents = $priceCents;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getPriceCents(): int
    {
        return $this->priceCents;
    }
}
