<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Panier;
use App\Form\CommandeFrontType;
use App\Service\StripeService;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'commande_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get commandes using direct SQL to avoid ORM loading issues
        $conn = $entityManager->getConnection();
        $sql = 'SELECT c.id, c.email, c.date_commande, c.localisation, c.id_panier, 
                p.prix_total FROM commande c 
                LEFT JOIN panier p ON c.id_panier = p.id';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $commandesData = $result->fetchAllAssociative();
        
        // Convert to array of objects for easier template handling
        $commandes = [];
        foreach ($commandesData as $commandeData) {
            $commande = new \stdClass();
            $commande->id = $commandeData['id'];
            $commande->email = $commandeData['email'];
            $commande->dateCommande = new \DateTime($commandeData['date_commande']);
            $commande->localisation = $commandeData['localisation'];
            
            // Add panier data if available
            if ($commandeData['id_panier'] && $commandeData['prix_total'] !== null) {
                $commande->panier = new \stdClass();
                $commande->panier->prixTotal = $commandeData['prix_total'];
            } else {
                $commande->panier = null;
            }
            
            $commandes[] = $commande;
        }

        return $this->render('commande/back/list.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/new', name: 'commande_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $commande->setIdPanier($commande->getPanier()->getId());
            $entityManager->persist($commande);
            $entityManager->flush();
            
            return $this->redirectToRoute('commande_index');
        }
        
        return $this->render('commande/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'commande_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $commande = $entityManager
            ->getRepository(Commande::class)
            ->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }
        
        // Get cart products using direct SQL to avoid ORM loading issues
        $panierProduits = [];
        if ($commande->getIdPanier()) {
            $conn = $entityManager->getConnection();
            $sql = '
                SELECT pp.id_panier, pp.id_produit, pp.quantite, 
                p.nom, p.description, p.prix, p.photo
                FROM panier_produit pp
                JOIN produit p ON pp.id_produit = p.id
                WHERE pp.id_panier = :panierId
            ';
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery(['panierId' => $commande->getIdPanier()]);
            $panierProduits = $resultSet->fetchAllAssociative();
        }

        return $this->render('commande/back/view.html.twig', [
            'commande' => $commande,
            'panierProduits' => $panierProduits,
        ]);
    }

    #[Route('/{id}/edit', name: 'commande_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $commande = $entityManager
            ->getRepository(Commande::class)
            ->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande->setIdPanier($commande->getPanier()->getId());
            $entityManager->flush();

            return $this->redirectToRoute('commande_index');
        }
        
        // Get cart products using direct SQL to avoid ORM loading issues
        $panierProduits = [];
        if ($commande->getIdPanier()) {
            $conn = $entityManager->getConnection();
            $sql = '
                SELECT pp.id_panier, pp.id_produit, pp.quantite, 
                p.nom, p.description, p.prix, p.photo
                FROM panier_produit pp
                JOIN produit p ON pp.id_produit = p.id
                WHERE pp.id_panier = :panierId
            ';
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery(['panierId' => $commande->getIdPanier()]);
            $panierProduits = $resultSet->fetchAllAssociative();
        }

        return $this->render('commande/back/edit.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
            'panierProduits' => $panierProduits
        ]);
    }

    #[Route('/{id}/delete', name: 'commande_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        try {
            // Use direct SQL to delete the commande
            $conn = $entityManager->getConnection();
            $sql = 'DELETE FROM commande WHERE id = :id';
            $stmt = $conn->prepare($sql);
            $stmt->executeStatement(['id' => $id]);

            $this->addFlash('success', 'Order deleted successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting order: ' . $e->getMessage());
        }

        return $this->redirectToRoute('commande_index');
    }

    #[Route('/create-from-panier/{panierId}', name: 'commande_create_from_panier')]
    public function createFromPanier(Request $request, EntityManagerInterface $entityManager, int $panierId, SessionInterface $session, StripeService $stripeService): Response
    {
        // Find the panier
        $panier = $entityManager->getRepository(Panier::class)->find($panierId);
        
        if (!$panier) {
            throw $this->createNotFoundException('Panier not found');
        }
        
        // Load panier products
        $conn = $entityManager->getConnection();
        $sql = '
            SELECT pp.id_panier, pp.id_produit, pp.quantite, 
            p.nom, p.description, p.prix, p.photo
            FROM panier_produit pp
            JOIN produit p ON pp.id_produit = p.id
            WHERE pp.id_panier = :panierId
        ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['panierId' => $panierId]);
        $panierProduits = $resultSet->fetchAllAssociative();
        
        // Check if panier is empty
        if (empty($panierProduits)) {
            $this->addFlash('danger', 'Votre panier est vide. Veuillez ajouter des produits avant de passer une commande.');
            return $this->redirectToRoute('panier_view');
        }
        
        // Create a new order
        $commande = new Commande();
        $commande->setPanier($panier);
        $commande->setIdPanier($panier->getId());
        $commande->setDateCommande(new \DateTime());
        
        // Create the form
        $form = $this->createForm(CommandeFrontType::class, $commande);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the order first to get an ID
            $entityManager->persist($commande);
            $entityManager->flush();
            
            // If it's an AJAX request, create a Stripe session and return session ID
            if ($request->isXmlHttpRequest()) {
                try {
                    $stripeSession = $stripeService->createCheckoutSession(
                        $commande->getId(),
                        $commande->getEmail(),
                        $panier->getPrixTotal(),
                        $panierProduits
                    );
                    
                    return new JsonResponse(['id' => $stripeSession->id]);
                } catch (\Exception $e) {
                    return new JsonResponse(['error' => $e->getMessage()], 500);
                }
            }
            
            // For non-AJAX requests, redirect to confirmation page
            return $this->redirectToRoute('commande_confirmation', [
                'id' => $commande->getId()
            ]);
        }
        
        return $this->render('commande/front/new.html.twig', [
            'form' => $form->createView(),
            'panier' => $panier,
            'panierProduits' => $panierProduits,
            'stripe_public_key' => $this->getParameter('stripe_public_key')
        ]);
    }
    
    #[Route('/confirmation/{id}', name: 'commande_confirmation')]
    public function confirmation(EntityManagerInterface $entityManager, Request $request, int $id, SessionInterface $session): Response
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);
        
        if (!$commande) {
            throw $this->createNotFoundException('Commande not found');
        }
        
        // If this was reached after successful payment, clear the session
        if ($request->query->has('payment_intent')) {
            $session->remove('panier_id');
        }
        
        return $this->render('commande/front/confirmation.html.twig', [
            'commande' => $commande
        ]);
    }
} 