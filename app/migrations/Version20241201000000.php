<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241201000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create products table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE products (
            sku VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(255) NOT NULL,
            price_cents INTEGER NOT NULL,
            PRIMARY KEY(sku)
        )');
        
        $this->addSql('CREATE INDEX idx_products_category ON products (category)');
        $this->addSql('CREATE INDEX idx_products_price ON products (price_cents)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE products');
    }
}
