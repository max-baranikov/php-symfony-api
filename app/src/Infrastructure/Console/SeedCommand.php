<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Infrastructure\Persistence\Doctrine\ProductEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed')]
class SeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Seed the database with sample products')
            ->addOption('bulk', null, InputOption::VALUE_OPTIONAL, 'Number of additional products to generate', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var int|numeric-string|null $bulkOpt */
        $bulkOpt = $input->getOption('bulk');
        $bulk = $bulkOpt === null ? 0 : (int) $bulkOpt;

        // Clear existing products
        $this->entityManager->createQuery('DELETE FROM App\Infrastructure\Persistence\Doctrine\ProductEntity')->execute();

        // Insert the 5 given products
        $products = [
            ['sku' => '000001', 'name' => 'BV Lean leather ankle boots', 'category' => 'boots', 'price' => 89000],
            ['sku' => '000002', 'name' => 'BV Lean leather ankle boots', 'category' => 'boots', 'price' => 99000],
            ['sku' => '000003', 'name' => 'Ashlington leather ankle boots', 'category' => 'boots', 'price' => 71000],
            ['sku' => '000004', 'name' => 'Naima embellished suede sandals', 'category' => 'sandals', 'price' => 79500],
            ['sku' => '000005', 'name' => 'Nathane leather sneakers', 'category' => 'sneakers', 'price' => 59000],
        ];

        foreach ($products as $productData) {
            $product = new ProductEntity(
                $productData['sku'],
                $productData['name'],
                $productData['category'],
                $productData['price']
            );
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();

        // Generate bulk data if requested
        if ($bulk > 0) {
            $categories = ['boots', 'sandals', 'sneakers', 'shoes', 'heels'];
            for ($i = 1; $i <= $bulk; $i++) {
                $sku = sprintf('%06d', $i + 5);
                $category = $categories[array_rand($categories)];
                $price = rand(20000, 150000); // Random price between 200€ and 1500€

                $product = new ProductEntity(
                    $sku,
                    "Generated Product {$sku}",
                    $category,
                    $price
                );
                $this->entityManager->persist($product);

                if ($i % 1000 === 0) {
                    $this->entityManager->flush();
                }
            }
            $this->entityManager->flush();
        }

        $output->writeln(sprintf('Seeded %d products', count($products) + $bulk));

        return Command::SUCCESS;
    }
}
