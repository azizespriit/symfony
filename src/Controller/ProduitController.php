<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'produit_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll();

        return $this->render('produit/back/list.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/catalogue', name: 'produit_catalogue')]
    public function catalogue(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findBy([], ['nom' => 'ASC']);

        return $this->render('produit/front/produitaffichage.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/new', name: 'produit_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('produit_images_directory'),
                        $newFilename
                    );
                    
                    $produit->setPhoto($newFilename);
                } catch (FileException $e) {
                    // Handle exception
                }
            }
            
            $entityManager->persist($produit);
            $entityManager->flush();
            
            return $this->redirectToRoute('produit_index');
        }
        
        return $this->render('produit/back/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ajouter', name: 'produit_add_front')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('produit_images_directory'),
                        $newFilename
                    );
                    
                    $produit->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur s\'est produite lors du téléchargement de l\'image');
                }
            }
            
            $entityManager->persist($produit);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le produit a été ajouté avec succès!');
            
            return $this->redirectToRoute('produit_catalogue');
        }
        
        return $this->render('produit/front/addproduct.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/detail/{id}', name: 'produit_detail')]
    public function detail(EntityManagerInterface $entityManager, int $id): Response
    {
        $produit = $entityManager
            ->getRepository(Produit::class)
            ->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        // Find some related products (example: 3 random products)
        $relatedProducts = $entityManager
            ->getRepository(Produit::class)
            ->findBy(
                ['id' => ['!=' => $id]], // exclude current product
                ['id' => 'DESC'],
                3 // limit to 3 products
            );

        return $this->render('produit/front/produitdetail.twig', [
            'produit' => $produit,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    #[Route('/{id}', name: 'produit_show', requirements: ['id' => '\d+'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $produit = $entityManager
            ->getRepository(Produit::class)
            ->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Product not found');
        }
        
        // Get cart count using direct SQL instead of ORM relationship
        $conn = $entityManager->getConnection();
        $sql = 'SELECT COUNT(*) as cart_count FROM panier_produit WHERE id_produit = :produitId';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['produitId' => $id]);
        $result = $resultSet->fetchAssociative();
        $cartCount = $result['cart_count'] ?? 0;

        return $this->render('produit/back/view.html.twig', [
            'produit' => $produit,
            'cartCount' => $cartCount
        ]);
    }

    #[Route('/{id}/edit', name: 'produit_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id, SluggerInterface $slugger): Response
    {
        $produit = $entityManager
            ->getRepository(Produit::class)
            ->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Product not found');
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('produit_images_directory'),
                        $newFilename
                    );
                    
                    $produit->setPhoto($newFilename);
                } catch (FileException $e) {
                    // Handle exception
                }
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('produit_index');
        }
        
        // Get cart count using direct SQL instead of ORM relationship
        $conn = $entityManager->getConnection();
        $sql = 'SELECT COUNT(*) as cart_count FROM panier_produit WHERE id_produit = :produitId';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['produitId' => $id]);
        $result = $resultSet->fetchAssociative();
        $cartCount = $result['cart_count'] ?? 0;

        return $this->render('produit/back/edit.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
            'cartCount' => $cartCount
        ]);
    }

    #[Route('/{id}/delete', name: 'produit_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $produit = $entityManager
            ->getRepository(Produit::class)
            ->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Product not found');
        }

        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute('produit_index');
    }
} 