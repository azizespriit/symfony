<?php

namespace App\Controller;
use Knp\Snappy\Pdf;

use App\Entity\Plan;
use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
use App\Form\PlanType;
use App\Repository\PlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\JourRepository;
use Doctrine\Persistence\ManagerRegistry; // Import correct
use Dompdf\Dompdf;
use Dompdf\Options;



#[Route('/plan')]
final class PlanController extends AbstractController{
    #[Route('/all/{id}', name: 'app_plan_by_objectif', methods: ['GET'])]
    public function plansByObjectif(int $id, PlanRepository $planRepository, ObjectifRepository $objectifRepository): Response
    {
        $plans = $planRepository->findByObjectifId($id);
        $objectif = $objectifRepository->find($id); // on récupère l'objectif aussi
    
        return $this->render('plan/index.html.twig', [
            'plans' => $plans,
            'objectif' => $objectif, // on envoie l'objectif
        ]);
    }
    #[Route('/all1/{id}', name: 'app_plan_by_objectif1', methods: ['GET'])]
    public function plansByObjectif1(int $id, PlanRepository $planRepository, ObjectifRepository $objectifRepository): Response
    {
        $plans = $planRepository->findByObjectifId($id);
        $objectif = $objectifRepository->find($id); // on récupère l'objectif aussi
    
        return $this->render('objectif/front/pln.html.twig', [
            'plans' => $plans,
            'objectif' => $objectif, // on envoie l'objectif
        ]);
    }
    private Pdf $pdf;
    private ManagerRegistry $doctrine;

    public function __construct(Pdf $pdf, ManagerRegistry $doctrine)
    {
        $this->pdf = $pdf;
        $this->doctrine = $doctrine;
    }

  

  
    #[Route('/plans/export/{id}', name: 'export_plans')]
    public function exportPlans(int $id): Response
    {
        // 1. Récupérer l'objectif par son ID
        $objectif = $this->doctrine->getRepository(Objectif::class)->find($id);
    
        if (!$objectif) {
            throw $this->createNotFoundException("Objectif avec l'ID $id non trouvé.");
        }
    
        // 2. Récupérer les plans associés à cet objectif
        $plans = $this->doctrine->getRepository(Plan::class)->findBy(['id_obj' => $objectif]);
    
        if (empty($plans)) {
            throw $this->createNotFoundException("Aucun plan trouvé pour l'objectif : " . $objectif->getNom());
        }
    
        // 3. Générer le HTML depuis Twig
        $html = $this->renderView('plan/plans_pdf.html.twig', [
            'plans' => $plans,
            'objectif' => $objectif,
        ]);
    
        // 4. Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');  // Police par défaut
        $options->set('isRemoteEnabled', true); // Permet d'inclure des images externes/CSS
    
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // 5. Retourner le PDF en stream
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="plans_objectif_' . $id . '.pdf"',  // Téléchargement forcé
            ]
        );
    }
    #[Route('/new/{id}', name: 'app_plan_new', methods: ['GET', 'POST'])]
    public function new(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ObjectifRepository $objectifRepository,
        JourRepository $jourRepository
    ): Response {
        $objectif = $objectifRepository->find($id);
    
        if (!$objectif) {
            throw $this->createNotFoundException('Objectif non trouvé');
        }
    
        $plan = new Plan();
        $plan->setIdObj($objectif);
    
        // Créer le formulaire avec l'option objectif
        $form = $this->createForm(PlanType::class, $plan, [
            'objectif' => $objectif,
        ]);
        
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'entité Jour sélectionnée
            $jour = $form->get('Jour')->getData();
            
            // Persister le plan
            $entityManager->persist($plan);
            
            // Supprimer le jour s'il existe
            if ($jour) {
                $entityManager->remove($jour);
            }
            
            // Exécuter les opérations
            $entityManager->flush();
    
            return $this->redirectToRoute('app_plan_by_objectif', ['id' => $objectif->getId()]);
        }
    
        return $this->render('plan/new.html.twig', [
            'plan' => $plan,
            'form' => $form->createView(),
            'objectif' => $objectif,
        ]);
    }
    
    #[Route('/{id}', name: 'app_plan_show', methods: ['GET'])]
    public function show(Plan $plan): Response
    {
        return $this->render('plan/show.html.twig', [
            'plan' => $plan,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_plan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Plan $plan, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_objectif_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plan/edit.html.twig', [
            'plan' => $plan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_plan_delete', methods: ['POST'])]
    public function delete(Request $request, Plan $plan, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plan->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plan);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_objectif_index', [], Response::HTTP_SEE_OTHER);
    }
}
