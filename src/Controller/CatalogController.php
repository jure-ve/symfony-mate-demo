<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/catalog')]
class CatalogController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/slow', name: 'catalog_slow')]
    public function slow(): JsonResponse
    {
        $data = [];
        foreach ($this->em->getRepository(Category::class)->findAll() as $category) {
            $data[$category->getName()] = $category->getProducts()
                ->map(fn (Product $p) => $p->getName())
                ->toArray();
        }

        return $this->json($data);
    }

    #[Route('/fast', name: 'catalog_fast')]
    public function fast(): JsonResponse
    {
        $categories = $this->em->createQueryBuilder()
            ->select('c', 'p')
            ->from(Category::class, 'c')
            ->leftJoin('c.products', 'p')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($categories as $category) {
            $data[$category->getName()] = $category->getProducts()
                ->map(fn (Product $p) => $p->getName())
                ->toArray();
        }

        return $this->json($data);
    }

    // Fallo a propósito: genera un log de error y una respuesta 500
    #[Route('/boom', name: 'catalog_boom')]
    public function boom(LoggerInterface $logger): never
    {
        $logger->error('Fallo simulado en el catálogo', ['sku' => 'X-001']);

        throw new \RuntimeException('Catálogo no disponible');
    }
}
