<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\ListProducts\DTO\ProductView;
use App\Application\ListProducts\ListProductsHandler;
use App\Application\ListProducts\ListProductsQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[Route('/products', name: 'products_')]
class ProductController extends AbstractController implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ListProductsHandler $listProductsHandler
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [];
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/products',
        summary: 'List products with discounts applied',
    )]
    #[OA\Response(response: 200, description: 'List of products with discounts applied', content: new OA\JsonContent(type: 'object', properties: [
        'products' => new OA\Property(type: 'array', maxItems: 5, items: new OA\Items(ref: ProductView::class)),
    ]))]
    #[OA\Parameter(name: 'category', in: 'query', description: 'Filter by category', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'priceLessThan', in: 'query', description: 'Filter by maximum price (in cents)', required: false, schema: new OA\Schema(type: 'integer', minimum: 0))]
    #[OA\Tag(name: 'Products')]
    public function list(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        $category = is_string($category) ? $category : null;

        $priceLessThan = $request->query->get('priceLessThan');
        $priceLessThan = is_numeric($priceLessThan) ? (int) $priceLessThan : null;

        $query = new ListProductsQuery($category, $priceLessThan);
        $products = $this->listProductsHandler->handle($query);

        return new JsonResponse(['products' => $products]);
    }
}
