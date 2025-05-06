<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Service\MailerService;
use App\Service\PdfGenerator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }

    #[Route('/admin', name: 'app__index', methods: ['GET'])]
    public function index2(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('reclamation/adminlist.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }
    #[Route('/search', name: 'app_reclamation_search', methods: ['GET'])]
    public function search(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $searchTerm = $request->query->get('q');
        $reclamations = $reclamationRepository->searchByNameOrEmail($searchTerm);
    
        return $this->render('reclamation/_table_rows.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
   #[Route('/reclamation/pdf', name: 'app_reclamation_pdf')]
    public function generatePdf(ReclamationRepository $reclamationRepository, PdfGenerator $pdfGenerator): Response
    {
        // Fetch all reclamations
        $reclamations = $reclamationRepository->findAll();

        // Generate unique filename
        $filename = 'reclamations_' . date('Ymd_His') . '.pdf';

        // Generate PDF and get the file path
        $pdfPath = $pdfGenerator->generateReclamationPdf($reclamations, $filename);

        // Return the PDF as a downloadable response
        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        // Optionally delete the file after sending
        $response->deleteFileAfterSend(true);

        return $response;
    }
    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,MailerService $mailer): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Fixer le user_id à 1
            $user = $entityManager->getReference('App\Entity\User', 1);
            $reclamation->setUserId($user);
    
            // Handle image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('reclamations_upload_dir'),
                        $newFilename
                    );
                    $reclamation->setImage($newFilename);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'Failed to upload image: '.$e->getMessage());
                }
            }
    
            // Définir la date de mise à jour
            $reclamation->setUpdated(new \DateTime());

            $entityManager->persist($reclamation);
            $entityManager->flush();
            $mailer->reclame($user->getEmail());
    
            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('reclamations_upload_dir'),
                        $newFilename
                    );
                    $reclamation->setImage($newFilename);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'Failed to upload image: '.$e->getMessage());
                }
            }            $reclamation->setUpdated(new \DateTime());
            
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    
    #[Route('/api/stats', name: 'app_reclamation_stats', methods: ['GET'])]
    public function stats(ReclamationRepository $reclamationRepository): Response
    {
        $stats = $reclamationRepository->getReclamationsByUser();
        
        return $this->render('reclamation/stat.html.twig', [
            'stats' => $stats,
        ]);
    }
}