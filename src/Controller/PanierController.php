<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Entity\Produit;
use App\Form\PanierType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'panier_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get paniers using direct SQL to avoid ORM loading issues
        $conn = $entityManager->getConnection();
        $sql = 'SELECT p.id, p.prix_total FROM panier p';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $paniersData = $result->fetchAllAssociative();
        
        // Convert to array of objects for easier template handling
        $paniers = [];
        foreach ($paniersData as $panierData) {
            $panier = new \stdClass();
            $panier->id = $panierData['id'];
            $panier->prixTotal = $panierData['prix_total'];
            
            // Check if the panier has an associated commande
            $sqlCommande = 'SELECT COUNT(*) as count FROM commande WHERE id_panier = :panierId';
            $stmtCommande = $conn->prepare($sqlCommande);
            $resultCommande = $stmtCommande->executeQuery(['panierId' => $panier->id]);
            $commandeData = $resultCommande->fetchAssociative();
            
            $panier->commande = $commandeData['count'] > 0;
            
            $paniers[] = $panier;
        }

        return $this->render('panier/back/list.html.twig', [
            'paniers' => $paniers,
        ]);
    }

    #[Route('/new', name: 'panier_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $panier = new Panier();
        
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($panier);
            $entityManager->flush();
            
            return $this->redirectToRoute('panier_index');
        }
        
        return $this->render('panier/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/add/{id}', name: 'panier_add_produit')]
    public function addProduit(Request $request, EntityManagerInterface $entityManager, int $id, SessionInterface $session): Response
    {
        // Get the product
        $produit = $entityManager->getRepository(Produit::class)->find($id);
        
        if (!$produit) {
            throw $this->createNotFoundException('Product not found');
        }
        
        // Get or create a cart for the current session
        $panierId = $session->get('panier_id');
        $panier = null;
        
        if ($panierId) {
            $panier = $entityManager->getRepository(Panier::class)->find($panierId);
        }
        
        // If no panier exists yet, create one automatically
        if (!$panier) {
            $panier = new Panier();
            $entityManager->persist($panier);
            $entityManager->flush();
            $session->set('panier_id', $panier->getId());
        }
        
        // Store product info in session for confirmation
        $session->set('pending_product', [
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'prix' => $produit->getPrix(),
            'photo' => $produit->getPhoto(),
            'quantite' => $request->query->get('quantite', 1)
        ]);
        
        // Redirect directly to confirm add
        return $this->redirectToRoute('panier_confirm_add');
    }
    
    #[Route('/confirm-add', name: 'panier_confirm_add')]
    public function confirmAdd(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $pendingProduct = $session->get('pending_product');
        $panierId = $session->get('panier_id');
        
        if (!$pendingProduct || !$panierId) {
            return $this->redirectToRoute('produit_catalogue');
        }
        
        $panier = $entityManager->getRepository(Panier::class)->find($panierId);
        $produit = $entityManager->getRepository(Produit::class)->find($pendingProduct['id']);
        
        if (!$panier || !$produit) {
            return $this->redirectToRoute('produit_catalogue');
        }
        
        // Check if product already exists in cart - using direct SQL query instead of findOneBy
        $conn = $entityManager->getConnection();
        $sql = 'SELECT quantite FROM panier_produit WHERE id_panier = :panierId AND id_produit = :produitId';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'panierId' => $panier->getId(),
            'produitId' => $produit->getId()
        ]);
        $result = $resultSet->fetchAssociative();
        
        if ($result) {
            // Product exists in cart, update quantity using direct SQL
            $nouvelleQuantite = $result['quantite'] + $pendingProduct['quantite'];
            $updateSql = 'UPDATE panier_produit SET quantite = :quantite WHERE id_panier = :panierId AND id_produit = :produitId';
            $conn->executeStatement($updateSql, [
                'quantite' => $nouvelleQuantite,
                'panierId' => $panier->getId(),
                'produitId' => $produit->getId()
            ]);
        } else {
            // Create new cart item
            $panierProduit = new PanierProduit();
            $panierProduit->setPanier($panier);
            $panierProduit->setProduit($produit);
            $panierProduit->setQuantite($pendingProduct['quantite']);
            
            // Also set the scalar properties required by the database
            $panierProduit->setIdPanier($panier->getId());
            $panierProduit->setIdProduit($produit->getId());
            
            $entityManager->persist($panierProduit);
        }
        
        // Update cart total price
        $newTotal = $panier->getPrixTotal() + ($produit->getPrix() * $pendingProduct['quantite']);
        $panier->setPrixTotal($newTotal);
        
        $entityManager->flush();
        
        // Clear the pending product from session
        $session->remove('pending_product');
        
        // Flash message to confirm the product was added
        $this->addFlash('success', 'Le produit a été ajouté à votre panier avec succès.');
        
        // Redirect to view cart page
        return $this->redirectToRoute('panier_view');
    }
    
    #[Route('/cancel-add', name: 'panier_cancel_add')]
    public function cancelAdd(SessionInterface $session): Response
    {
        // Remove pending product from session
        $session->remove('pending_product');
        
        // Redirect back to products page
        return $this->redirectToRoute('produit_catalogue');
    }
    
    #[Route('/view', name: 'panier_view')]
    public function viewCart(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Get panier ID from session
        $panierId = $session->get('panier_id');
        $panier = null;
        $panierProduits = [];
        
        if ($panierId) {
            $panier = $entityManager->getRepository(Panier::class)->find($panierId);
            
            if ($panier) {
                // Get cart products using direct SQL
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
            }
        }
        
        // If no panier exists yet or it's empty
        if (!$panier || count($panierProduits) === 0) {
            $this->addFlash('info', 'Votre panier est vide');
        }
        
        return $this->render('panier/front/panierview.twig', [
            'panier' => $panier,
            'panierProduits' => $panierProduits
        ]);
    }

    #[Route('/{id}', name: 'panier_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $panier = $entityManager
            ->getRepository(Panier::class)
            ->find($id);

        if (!$panier) {
            throw $this->createNotFoundException('Cart not found');
        }
        
        // Get cart products using direct SQL to avoid ORM loading issues
        $conn = $entityManager->getConnection();
        $sql = '
            SELECT pp.id_panier, pp.id_produit, pp.quantite, 
            p.nom, p.description, p.prix, p.photo
            FROM panier_produit pp
            JOIN produit p ON pp.id_produit = p.id
            WHERE pp.id_panier = :panierId
        ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['panierId' => $id]);
        $panierProduits = $resultSet->fetchAllAssociative();

        return $this->render('panier/back/view.html.twig', [
            'panier' => $panier,
            'panierProduits' => $panierProduits,
        ]);
    }

    #[Route('/{id}/edit', name: 'panier_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $panier = $entityManager
            ->getRepository(Panier::class)
            ->find($id);

        if (!$panier) {
            throw $this->createNotFoundException('Cart not found');
        }

        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('panier_index');
        }
        
        // Get cart products using direct SQL to avoid ORM loading issues
        $conn = $entityManager->getConnection();
        $sql = '
            SELECT pp.id_panier, pp.id_produit, pp.quantite, 
            p.nom, p.description, p.prix, p.photo
            FROM panier_produit pp
            JOIN produit p ON pp.id_produit = p.id
            WHERE pp.id_panier = :panierId
        ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['panierId' => $id]);
        $panierProduits = $resultSet->fetchAllAssociative();
        
        // Get all available products for the dropdown
        $productsSql = 'SELECT id, nom, prix FROM produit WHERE stock > 0 ORDER BY nom';
        $productsStmt = $conn->prepare($productsSql);
        $productsResult = $productsStmt->executeQuery();
        $availableProducts = $productsResult->fetchAllAssociative();

        return $this->render('panier/back/edit.html.twig', [
            'form' => $form->createView(),
            'panier' => $panier,
            'panierProduits' => $panierProduits,
            'availableProducts' => $availableProducts
        ]);
    }

    #[Route('/{id}/delete', name: 'panier_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $panier = $entityManager
            ->getRepository(Panier::class)
            ->find($id);

        if (!$panier) {
            throw $this->createNotFoundException('Cart not found');
        }

        $entityManager->remove($panier);
        $entityManager->flush();

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/remove/{produitId}', name: 'panier_remove_produit')]
    public function removeProduit(EntityManagerInterface $entityManager, SessionInterface $session, int $produitId): Response
    {
        $panierId = $session->get('panier_id');
        
        if (!$panierId) {
            return $this->redirectToRoute('panier_view');
        }
        
        $panier = $entityManager->getRepository(Panier::class)->find($panierId);
        
        if (!$panier) {
            return $this->redirectToRoute('panier_view');
        }
        
        // Get product price and quantity before removing
        $conn = $entityManager->getConnection();
        $sql = '
            SELECT pp.quantite, p.prix 
            FROM panier_produit pp
            JOIN produit p ON pp.id_produit = p.id
            WHERE pp.id_produit = :produitId AND pp.id_panier = :panierId
        ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'produitId' => $produitId,
            'panierId' => $panierId
        ]);
        $result = $resultSet->fetchAssociative();
        
        if (!$result) {
            return $this->redirectToRoute('panier_view');
        }
        
        // Update cart total price
        $itemTotal = $result['prix'] * $result['quantite'];
        $newTotal = $panier->getPrixTotal() - $itemTotal;
        $panier->setPrixTotal(max(0, $newTotal)); // Ensure total is not negative
        
        // Remove the product from the cart using direct SQL
        $deleteSql = 'DELETE FROM panier_produit WHERE id_produit = :produitId AND id_panier = :panierId';
        $conn->executeStatement($deleteSql, [
            'produitId' => $produitId,
            'panierId' => $panierId
        ]);
        
        $entityManager->flush();
        
        // Redirect to view cart page
        return $this->redirectToRoute('panier_view');
    }
} 