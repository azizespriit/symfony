<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Commande;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll();

        return $this->render('base.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll();
            
        $commandes = $entityManager
            ->getRepository(Commande::class)
            ->findAll();

        $totalProducts = count($produits);
        $totalOrders = count($commandes);
        
        $lowStockProducts = $entityManager
            ->getRepository(Produit::class)
            ->findBy(['stock' => 0]);
            
        $lowStockCount = count($lowStockProducts);

        return $this->render('baseback.html.twig', [
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'low_stock_count' => $lowStockCount,
            'recent_orders' => array_slice($commandes, 0, 5),
            'low_stock_products' => $lowStockProducts,
        ]);
    }
} 