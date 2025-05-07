<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class CommentaireController extends AbstractController
{
    #[Route('/commantaire', name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    #[Route('/commantaire/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setUser($this->getUser());
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Comment added successfully!');
            return $this->redirectToRoute('app_commentaire_index');
        }

        return $this->render('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/commantaire/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/commantaire/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function editCommantaire(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($commentaire->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Comment updated successfully!');
            return $this->redirectToRoute('app_commentaire_index');
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/commentaires/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Commentaire $commentaire,
        EntityManagerInterface $entityManager
    ): Response {

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Comment updated successfully!');
            return $this->redirectToRoute('app_publication_show', [
                'id' => $commentaire->getPublication()->getId(),
                '_fragment' => 'comment-' . $commentaire->getId()
            ]);
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    // #[Route('/{id}', name: 'app_commentaire_delete1', methods: ['POST'])]
    // public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    // {
    //     if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
    //         $entityManager->remove($commentaire);
    //         $entityManager->flush();
    //         $this->addFlash('success', 'Comment deleted successfully!');
    //     }

    //     return $this->redirectToRoute('backoffice_publication_index');
    // }

    #[Route('/commentaire/{id}/delete', name: 'app_commentaire_delete1', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
            $this->addFlash('success', 'Comment deleted successfully!');
        }

        return $this->redirectToRoute('backoffice_publication_index');
    }

}