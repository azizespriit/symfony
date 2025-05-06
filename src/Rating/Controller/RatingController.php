<?php 
namespace App\Rating\Controller;

use App\Rating\Entity\Rating;
use App\Rating\Form\RatingType;
use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RatingController extends AbstractController
{
    #[Route('/rating/add/{objectifId}', name: 'rating_add', methods: ['GET', 'POST'])]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        int $objectifId,
        ObjectifRepository $objectifRepository
    ): Response {
        // 1. Récupération de l'objectif
        $objectif = $objectifRepository->find($objectifId);
        
        if (!$objectif) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Objectif non trouvé'
                ], 404);
            }
            
            $this->addFlash('error', 'Objectif non trouvé');
            return $this->redirectToRoute('app_objectif_index');
        }

        // 2. Création du rating
        $rating = new Rating();
        $rating->setObjectif($objectif);

        // 3. Gestion des requêtes AJAX
        if ($request->isXmlHttpRequest()) {
            $score = (int) $request->request->get('rating');
            
            // Validation
            if ($score < 1 || $score > 5) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'La note doit être entre 1 et 5'
                ], 400);
            }

            try {
                $rating->setScore($score);
                $em->persist($rating);
                $em->flush();

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Note enregistrée avec succès',
                    'score' => $score
                ]);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
                ], 500);
            }
        }

        // 4. Gestion du formulaire HTML
        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($rating);
                $em->flush();
                
                $this->addFlash('success', 'Note enregistrée avec succès');
                return $this->redirectToRoute('app_objectif_front');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
            }
        }

        // 5. Affichage du formulaire
        return $this->render('add.html.twig', [
            'form' => $form->createView(),
            'objectif' => $objectif,
        ]);
    }
}