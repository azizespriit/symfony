<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\Commentaire;
use App\Entity\Reaction;
use App\Entity\User;
use App\Form\PublicationType;
use App\Form\CommentaireType;
use App\Repository\PublicationRepository;
use App\Repository\CommentaireRepository;
use App\Repository\UserRepository;
use App\Repository\ReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Service\GeminiMedicalService;
use Dompdf\Dompdf;



class PublicationController extends AbstractController
{

    private $entityManager;

    // Injection du gestionnaire d'entités via le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/publications', name: 'app_publications', methods: ['GET'])]
    public function publications(Request $request, PublicationRepository $publicationRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $order = strtoupper($request->query->get('order', 'DESC'));
    
        // Vérifier que l'ordre est valide
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
    
        // Récupérer les publications selon la recherche et l'ordre
        if ($searchTerm) {
            $publications = $publicationRepository->findByContenu($searchTerm, $order);
        } else {
            $publications = $publicationRepository->findAllOrderedByDate($order);
        }
    
        // Vérifier si la requête est une requête AJAX
        if ($request->isXmlHttpRequest()) {
            return $this->render('publication/_publication_list.html.twig', [
                'publications' => $publications,
                'searchTerm' => $searchTerm,
                'order' => $order,
            ]);
        }
    
        // Retourner la vue de la page principale
        return $this->render('publication/list.html.twig', [
            'publications' => $publications,
            'searchTerm' => $searchTerm,
            'order' => $order,
        ]);
    }
    
    private function getStaticUser(EntityManagerInterface $entityManager): User
    {
        return $entityManager->getRepository(User::class)->find(14);
    }
    private function customDecode(string $input = null): ?string
    {
        if ($input === null) {
            return null;
        }
        // Remplacer explicitement %20 par un espace
        $decoded = str_replace('%20', ' ', $input);
        // Optionnel : utiliser urldecode pour gérer d'autres caractères encodés
        return urldecode($decoded);
    }
    #[Route('/publications/new', name: 'app_publication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $publication = new Publication();
        
        // Pre-fill form with decoded query parameters if present
        if ($request->query->has('content')) {
            dump($request->query->get('content'));
            $publication->setContenu($this->customDecode($request->query->get('content')));
        }
        if ($request->query->has('description')) {
            $publication->setDescription($this->customDecode($request->query->get('description')));
        }
    
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->has('publish')) {
                $publication->setDatePub(new \DateTime());
                $publication->setUser($this->getStaticUser($entityManager));
                
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
                        copy(
                            $this->getParameter('publications_directory').'/'.$newFilename,
                            $this->getParameter('custom_images_directory').'/'.$newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'There was a problem uploading your image');
                    }
            
                    $publication->setImageUrl($newFilename);
                }
                $entityManager->persist($publication);
                $entityManager->flush();
    
                $this->addFlash('success', 'Publication created successfully!');
                return $this->redirectToRoute('app_publications');
            } elseif ($request->request->has('save_draft')) {
                // Update the form with the submitted content and reload the page
                $this->addFlash('success', 'Draft saved. You can now generate a description.');
                return $this->redirectToRoute('app_publication_new', [
                    'content' => urlencode($publication->getContenu()), // Encoder pour l'URL
                    'description' => urlencode($publication->getDescription()), // Encoder pour l'URL
                ]);
            }
        }
    
        return $this->render('publication/new.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/publications/generate-description/{content}/', name: 'app_publication_generate_description', methods: ['GET'])]
    public function generateDescription(string $content, GeminiMedicalService $geminiService): Response
    {
        if (empty($content) || $content === 'placeholder') {
            $this->addFlash('error', 'Please enter content before generating a description.');
            return $this->redirectToRoute('app_publication_new');
        }
        $decodedContent = $this->customDecode($content);
        try {
            $description = $geminiService->generatePublicationDescription($content);
            $decodedDescription = $this->customDecode($description);
            // Create a new Publication object to pass to the form
            $publication = new Publication();
            $publication->setContenu($decodedContent);
            $publication->setDescription($decodedDescription);

            $form = $this->createForm(PublicationType::class, $publication);

            return $this->render('publication/newwithdesc.html.twig', [
                'publication' => $publication,
                'form' => $form->createView(),
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'A technical error occurred: ' . $e->getMessage());
            return $this->redirectToRoute('app_publication_new');
        }
    }
    #[Route('/publications/{id}', name: 'app_publication_show', methods: ['GET', 'POST'])]
    public function show(
        Publication $publication, 
        Request $request, 
        EntityManagerInterface $entityManager,
        CommentaireRepository $commentaireRepository,
        PublicationRepository $publicationRepository,
        ReactionRepository $reactionRepository
    ): Response {
        $user = $this->getStaticUser($entityManager);
        $commentaire = new Commentaire();
        $reactionCounts = $reactionRepository->countReactionsGroupedByType($publication->getId());
        
        $userReaction = $reactionRepository->findOneBy([
            'publication' => $publication,
            'user' => $user
        ]);
        
        $editCommentId = $request->query->get('edit');
        $editComment = null;
        $editForm = null;
        
        if ($editCommentId) {
            $editComment = $commentaireRepository->find($editCommentId);
            if ($editComment && $editComment->getUser() === $user) {
                $commentaire = $editComment;
            }
        }
        
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$commentaire->getId()) {
                $commentaire->setUser($user);
                $commentaire->setPublication($publication);
            }
            
            $entityManager->persist($commentaire);
            $entityManager->flush();
    
            $this->addFlash('success', 'Comment ' . ($commentaire->getId() ? 'updated' : 'added') . ' successfully!');
            return $this->redirectToRoute('app_publication_show', [
                'id' => $publication->getId(),
                '_fragment' => 'comment-' . $commentaire->getId()
            ]);
        }
    
        if ($editComment) {
            $editForm = $this->createForm(CommentaireType::class, $editComment)->createView();
        }
    
        $relatedPublications = $publicationRepository->createQueryBuilder('p')
            ->where('p.id != :currentId')
            ->setParameter('currentId', $publication->getId())
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    
        return $this->render('publication/show.html.twig', [
            'publication' => $publication,
            'commentaires' => $commentaireRepository->findByPublication($publication),
            'form' => $form->createView(),
            'editForm' => $editForm,
            'relatedPublications' => $relatedPublications,
            'reactionCounts' => $reactionCounts,
            'userReaction' => $userReaction,
            'editComment' => $editComment,
            'staticUser' => $user
        ]);
    }
    #[Route('/cm/translate-comment', name: 'app_translate_comment', methods: ['POST'])]
    public function translateComment(Request $request,GeminiMedicalService $geminiService): Response
    {
        $commentId = $request->request->get('comment_id');
        $text = $request->request->get('text');

        if (empty($text) || empty($commentId)) {
            $this->addFlash('error', 'No text or comment ID provided.');
            return $this->redirectToRoute('app_publication_show', ['id' => $request->request->get('publication_id')]);
        }

        try {
            // Decode text to handle %20 and other encodings
            $decodedText = urldecode($text);
            // Translate from French to English
            $translatedText = $geminiService->translateFrenchToEnglish($decodedText);
            // Redirect to comment show page with translated text
            return $this->redirectToRoute('app_commentaire_show', [
                'id' => $commentId,
                'translated_text' => $translatedText
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Translation failed: ' . $e->getMessage());
            return $this->redirectToRoute('app_publication_show', ['id' => $request->request->get('publication_id')]);
        }
    }

    #[Route('/commantaire/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function showw(Commentaire $commentaire, Request $request): Response
    {
        // Get translated text from query parameter, if provided
        $translatedText = $request->query->get('translated_text');

        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
            'translated_text' => $translatedText,
        ]);
    }
    #[Route('/publication/{id}/react/{type}', name: 'app_publication_react')]
    public function reactToPublication(
        Publication $publication,
        string $type,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getStaticUser($em);

        $reactionRepo = $em->getRepository(Reaction::class);
        $existingReaction = $reactionRepo->findOneBy([
            'user' => $user,
            'publication' => $publication,
        ]);

        if ($existingReaction) {
            if ($existingReaction->getType() === $type) {
                $em->remove($existingReaction);
            } else {
                $existingReaction->setType($type);
            }
        } else {
            $reaction = new Reaction();
            $reaction->setUser($user);
            $reaction->setPublication($publication);
            $reaction->setType($type);
            $em->persist($reaction);
        }

        $em->flush();

        return $this->redirectToRoute('app_publication_show', [
            'id' => $publication->getId()
        ]);
    }

    #[Route('/publications/{id}/edit', name: 'app_publication_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Publication $publication, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $staticUser = $this->getStaticUser($entityManager);
        if ($publication->getUser()->getId_user() !== $staticUser->getId_user()) {
            $this->addFlash('error', 'You are not authorized to edit this publication');
            return $this->redirectToRoute('app_publication_show', ['id' => $publication->getId()]);
        }

        $oldImage = $publication->getImageUrl();
        
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
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
                        copy(
                            $this->getParameter('publications_directory').'/'.$newFilename,
                            $this->getParameter('custom_images_directory').'/'.$newFilename
                        );
                        
                        if ($oldImage) {
                            $oldPublicPath = $this->getParameter('publications_directory').'/'.$oldImage;
                            $oldCustomPath = $this->getParameter('custom_images_directory').'/'.$oldImage;
                            
                            if (file_exists($oldPublicPath)) unlink($oldPublicPath);
                            if (file_exists($oldCustomPath)) unlink($oldCustomPath);
                        }
                        
                        $publication->setImageUrl($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'There was a problem uploading your image');
                        return $this->redirectToRoute('app_publication_edit', ['id' => $publication->getId()]);
                    }
                }
                
                $entityManager->flush();

                $this->addFlash('success', 'Publication updated successfully!');
                return $this->redirectToRoute('app_publication_show', [
                    'id' => $publication->getId(),
                    '_fragment' => 'publication-' . $publication->getId()
                ]);
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while updating the publication');
                return $this->redirectToRoute('app_publication_edit', ['id' => $publication->getId()]);
            }
        }

        return $this->render('publication/edit.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/publications/{id}/delete', name: 'app_publication_delete', methods: ['POST'])]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publication->getId(), $request->request->get('_token'))) {
            $em->remove($publication);
            $em->flush();
            $this->addFlash('success', 'Publication deleted!');
        }
        
        return $this->redirectToRoute('app_publications');
    }
    
    #[Route('/commentaire/{id}/delete', name: 'app_commentaire_delete', methods: ['POST'])]
    public function deleteCommentaire(
        Request $request,
        Commentaire $commentaire,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $commentaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
    
            $this->addFlash('success', 'Comment deleted successfully!');
        }
    
        return $this->redirectToRoute('app_publication_show', ['id' => $commentaire->getPublication()->getId()]);
    }
    
    #[Route('/PubBack/{id}', name: 'backoffice_publication_show', methods: ['GET'])]
    public function showBack(
        Publication $publication, 
        PublicationRepository $publicationRepository,
        ReactionRepository $reactionRepository
    ): Response {
        $publication = $publicationRepository->findByP($publication->getId());
        $reactionCounts = $reactionRepository->countReactionsGroupedByType($publication->getId());

        return $this->render('publication/showBack.html.twig', [
            'publication' => $publication,
            'reactionCounts' => $reactionCounts,
        ]);
    }

    #[Route('/dashboard/publicatons', name: 'backoffice_publication_index', methods: ['GET'])]
    public function indexBack(PublicationRepository $publicationRepository): Response
    {
        return $this->render('publication/indexBack.html.twig', [
            'publications' => $publicationRepository->findAllWithUser(),
        ]);
    }

    #[Route('/Pub/new', name: 'backoffice_publication_new', methods: ['GET', 'POST'])]
    public function newBack(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
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

    #[Route('/Pub/{id}/edit', name: 'backoffice_publication_edit', methods: ['GET', 'POST'])]
    public function editBack(
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

    #[Route('/Pub/{id}', name: 'backoffice_publication_delete', methods: ['POST'])]
    public function deleteBack(
        Request $request, 
        Publication $publication, 
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$publication->getId(), $request->request->get('_token'))) {
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
    #[Route('/vs/publications/statistics', name: 'app_publication_statistics', methods: ['GET'])]
    public function statistics(EntityManagerInterface $entityManager): Response
    {
        // Query to get the count of publications per user
        $query = $entityManager->createQuery(
            'SELECT u.id_user, u.firstName, u.lastName, COUNT(p.id) as publicationCount
             FROM App\Entity\User u
             LEFT JOIN u.publications p
             GROUP BY u.id_user, u.firstName, u.lastName
             ORDER BY publicationCount DESC'
        );
        $usersWithPublicationCount = $query->getResult();

        // Query to get the total number of publications
        $totalPublications = $entityManager->createQuery(
            'SELECT COUNT(p.id) as total
             FROM App\Entity\Publication p'
        )->getSingleScalarResult();

        // Combine firstName and lastName in the result
        $usersWithPublicationCount = array_map(function ($user) {
            return [
                'id' => $user['id_user'],
                'fullName' => $user['firstName'] . ' ' . $user['lastName'],
                'publicationCount' => $user['publicationCount'],
            ];
        }, $usersWithPublicationCount);

        return $this->render('publication/stat.html.twig', [
            'usersWithPublicationCount' => $usersWithPublicationCount,
            'totalPublications' => $totalPublications,
        ]);
    }

    
   

    #[Route('/mes-publications/{id}', name: 'app_mes_publications')]
    public function mesPublications(
        int $id,
        UserRepository $userRepository,
        PublicationRepository $publicationRepository,
        CommentaireRepository $commentaireRepository
    ): Response {
        $user = $userRepository->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
    
        $publications = $publicationRepository->findByUser($user);
        $commentaires = $commentaireRepository->findByUser($user);
    
        return $this->render('publication/mes_publications.html.twig', [
            'publications' => $publications,
            'commentaires' => $commentaires,
            'user' => $user,
        ]);
    }
   // Exemple de méthode dans le contrôleur pour générer le PDF avec Dompdf
   
   #[Route('/publication/{id}/pdf', name: 'publication_pdf')]
   public function generatePdf(int $id, PublicationRepository $repo): Response
   {
       $publication = $repo->find($id);
       if (!$publication) {
           throw $this->createNotFoundException('Publication non trouvée.');
       }
   
       // chemin absolu sur le disque
       $publicationsDir = $this->getParameter('publications_directory');
       $filename       = $publication->getImageUrl();
       $absolutePath   = $publicationsDir . DIRECTORY_SEPARATOR . $filename;
   
       // si le fichier existe, on préfixe "file://"
       $imagePath = file_exists($absolutePath)
           ? 'file://'.$absolutePath
           : null;
   
       $html = $this->renderView('publication/pdf.html.twig', [
           'publication' => $publication,
           'imagePath'   => $imagePath,
       ]);
   
       $options = new Options();
       $options->set('defaultFont', 'Arial');
       $options->set('isRemoteEnabled', true);
       $options->set('chroot', $this->getParameter('kernel.project_dir').'/public');
   
       $dompdf = new Dompdf($options);
       $dompdf->loadHtml($html);
       $dompdf->setPaper('A4', 'portrait');
       $dompdf->render();
   
       return new Response(
           $dompdf->output(),
           200,
           [
               'Content-Type'        => 'application/pdf',
               'Content-Disposition' => 'inline; filename="publication.pdf"',
           ]
       );
   }
   
   
}   
 
