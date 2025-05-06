<?php

namespace App\Controller;

use App\Entity\PanierProduit;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\PanierProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/panier-produit')]
class PanierProduitController extends AbstractController
{
    #[Route('/', name: 'panier_produit_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $panierProduits = $entityManager
            ->getRepository(PanierProduit::class)
            ->findAll();

        return $this->render('panier_produit/index.html.twig', [
            'panier_produits' => $panierProduits,
        ]);
    }

    #[Route('/new', name: 'panier_produit_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $panierProduit = new PanierProduit();
        
        $form = $this->createForm(PanierProduitType::class, $panierProduit);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the ID fields manually since we're using the entity objects
            $panierProduit->setIdPanier($panierProduit->getPanier()->getId());
            $panierProduit->setIdProduit($panierProduit->getProduit()->getId());
            
            $entityManager->persist($panierProduit);
            $entityManager->flush();
            
            return $this->redirectToRoute('panier_produit_index');
        }
        
        return $this->render('panier_produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'panier_produit_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $panierProduit = $entityManager
            ->getRepository(PanierProduit::class)
            ->find($id);

        if (!$panierProduit) {
            throw $this->createNotFoundException('Cart item not found');
        }

        return $this->render('panier_produit/show.html.twig', [
            'panier_produit' => $panierProduit,
        ]);
    }

    #[Route('/{id}/edit', name: 'panier_produit_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $panierProduit = $entityManager
            ->getRepository(PanierProduit::class)
            ->find($id);

        if (!$panierProduit) {
            throw $this->createNotFoundException('Cart item not found');
        }

        $form = $this->createForm(PanierProduitType::class, $panierProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the ID fields manually
            $panierProduit->setIdPanier($panierProduit->getPanier()->getId());
            $panierProduit->setIdProduit($panierProduit->getProduit()->getId());
            
            $entityManager->flush();

            return $this->redirectToRoute('panier_produit_index');
        }

        return $this->render('panier_produit/edit.html.twig', [
            'form' => $form->createView(),
            'panier_produit' => $panierProduit,
        ]);
    }

    #[Route('/{id}/delete', name: 'panier_produit_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $panierProduit = $entityManager
            ->getRepository(PanierProduit::class)
            ->find($id);

        if (!$panierProduit) {
            throw $this->createNotFoundException('Cart item not found');
        }

        $entityManager->remove($panierProduit);
        $entityManager->flush();

        return $this->redirectToRoute('panier_produit_index');
    }

    #[Route('/panier/{id_panier}', name: 'panier_produit_by_panier')]
    public function listByPanier(EntityManagerInterface $entityManager, int $id_panier): Response
    {
        $panier = $entityManager
            ->getRepository(Panier::class)
            ->find($id_panier);

        if (!$panier) {
            throw $this->createNotFoundException('Cart not found');
        }

        $panierProduits = $entityManager
            ->getRepository(PanierProduit::class)
            ->findBy(['id_panier' => $id_panier]);

        return $this->render('panier_produit/by_panier.html.twig', [
            'panier' => $panier,
            'panier_produits' => $panierProduits,
        ]);
    }
} 