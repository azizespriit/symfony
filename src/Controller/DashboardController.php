<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Publication;
use App\Form\PublicationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index1(): Response
    {
        return $this->render('/backoffice/base.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/dashboard/publicatons', name: 'backoffice_publication_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository): Response
    {
        return $this->render('publication/indexBack.html.twig', [
            'publications' => $publicationRepository->findAllWithUser(),
        ]);
    }

    #[Route('/new', name: 'backoffice_publication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $publication = new Publication();
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageUrl')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('publications_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was a problem uploading your image');
                }

                $publication->setImageUrl($newFilename);
            }

            $entityManager->persist($publication);
            $entityManager->flush();

            $this->addFlash('success', 'Publication created successfully!');
            return $this->redirectToRoute('backoffice_publication_index');
        }

        return $this->render('backoffice/publication/new.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'backoffice_publication_show', methods: ['GET'])]
    public function show(Publication $publication): Response
    {
        return $this->render('publication/showBack.html.twig', [
            'publication' => $publication,
        ]);
    }

    #[Route('/{id}/edit', name: 'backoffice_publication_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Publication $publication, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageUrl')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('publications_directory'),
                        $newFilename
                    );
                    
                    // Delete old image if exists
                    if ($publication->getImageUrl()) {
                        $oldImage = $this->getParameter('publications_directory').'/'.$publication->getImageUrl();
                        if (file_exists($oldImage)) {
                            unlink($oldImage);
                        }
                    }
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was a problem uploading your image');
                }

                $publication->setImageUrl($newFilename);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Publication updated successfully!');
            return $this->redirectToRoute('backoffice_publication_index');
        }

        return $this->render('backoffice/publication/edit.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'backoffice_publication_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Publication $publication, 
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$publication->getId(), $request->request->get('_token'))) {
            // Delete image file if exists
            if ($publication->getImageUrl()) {
                $imagePath = $this->getParameter('publications_directory').'/'.$publication->getImageUrl();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($publication);
            $entityManager->flush();
            
            $this->addFlash('success', 'Publication deleted successfully!');
        }

        return $this->redirectToRoute('backoffice_publication_index');
    }
}
