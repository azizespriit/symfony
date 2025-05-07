<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Reservation;
use App\Repository\CompetitionRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\Snappy\Pdf;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function indexHome(
        EntityManagerInterface $em,
        ReservationRepository $repo
    ): Response {
        // Récupération de toutes les compétitions
        $competitions = $em->getRepository(Competition::class)->findAll();
    
        // Récupération de toutes les réservations
        // $reservations = $em->getRepository(Reservation::class)->findAll();
    
        return $this->render('front/index.html.twig', [
            'competitions' => $competitions,
            // 'reservations' => $reservations,            
        ]);        
    }
    

    #[Route('/dashboard', name: 'app_back')]
    public function indexDash(): Response
    {
        return $this->render('back/index.html.twig');
    }


#[Route('/reservation/supprimer/{id}', name: 'app_supprimer_res', methods: ['POST'])]
public function supprimerReservation(int $id, ReservationRepository $repo, EntityManagerInterface $em, Request $request): Response
{
    $reservation = $repo->find($id);

    if (!$reservation) {
        throw $this->createNotFoundException('Réservation non trouvée.');
    }

    if ($this->isCsrfTokenValid('delete' . $reservation->getIdR(), $request->request->get('_token'))) {
        $em->remove($reservation);
        $em->flush();
    }

    return $this->redirectToRoute('app_home');
}


#[Route('/competition', name: 'app_competition')]
public function listCompetition(CompetitionRepository $repo, PaginatorInterface $paginator, Request $request): Response
{
    $queryBuilder = $repo->createQueryBuilder('c'); // Créer le QueryBuilder
    $competitionsQuery = $queryBuilder->getQuery(); // Créer la requête

    // Pagination
    $competitions = $paginator->paginate(
        $competitionsQuery, // La requête à paginer
        $request->query->getInt('page', 1), // Page actuelle (par défaut 1)
        5 // Nombre d'éléments par page
    );
    
    return $this->render('back/competition.html.twig', [
        'competitions' => $competitions,
    ]);
}


    // Route pour modifier une compétition
    #[Route('/competition/{id}/edit', name: 'app_modifier_competition')]
    public function editCompetition(int $id, CompetitionRepository $repo, Request $request, EntityManagerInterface $em): Response
    {
        $competition = $repo->find($id);
    
        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée.');
        }
    
        if ($request->isMethod('POST')) {
            $competition->setNom($request->request->get('nom'));
            $competition->setType($request->request->get('type'));
            $competition->setDateC(new \DateTime($request->request->get('dateC')));
            $competition->setLieuC($request->request->get('lieuC'));
            $competition->setNbPart($request->request->get('nbPart'));
            $competition->setDescription($request->request->get('description'));
    
            $em->flush();
    
            return $this->redirectToRoute('app_competition');
        }
    
        return $this->render('back/edit_competition.html.twig', [
            'competition' => $competition,
        ]);
    }
    

// Route pour supprimer une compétition
#[Route('/competition/{id}/delete', name: 'app_supprimer_competition', methods: ['POST'])]
public function deleteCompetition(int $id, CompetitionRepository $repo, EntityManagerInterface $em, Request $request): Response
{
    $competition = $repo->find($id);

    if (!$competition) {
        throw $this->createNotFoundException('Compétition non trouvée.');
    }

    // Vérification du token CSRF
    if ($this->isCsrfTokenValid('delete' . $competition->getIdC(), $request->request->get('_token'))) {
        $em->remove($competition);
        $em->flush();
    }

    return $this->redirectToRoute('app_competition');
}

#[Route('/reservation', name: 'app_reservation')]
public function listReservation(ReservationRepository $repo, PaginatorInterface $paginator, Request $request): Response
{
    $queryBuilder = $repo->createQueryBuilder('r'); // Créer le QueryBuilder
    $reservationsQuery = $queryBuilder->getQuery(); // Créer la requête

    // Pagination
    $reservations = $paginator->paginate(
        $reservationsQuery, // La requête à paginer
        $request->query->getInt('page', 1), // Page actuelle (par défaut 1)
        3 // Nombre d'éléments par page
    );
    
    return $this->render('back/reservation.html.twig', [
        'reservations' => $reservations,
    ]);
}


    #[Route('/reservation/pdf', name: 'app_reservation_pdf')]
    public function generatePdf(ReservationRepository $repo, Pdf $snappy): Response
    {
        // Récupérer les réservations
        $reservations = $repo->findAll();

        // Rendre le template pour le PDF
        $html = $this->renderView('back/reservation_pdf.html.twig', [
            'reservations' => $reservations,
        ]);

        // Générer le PDF à partir du HTML
        $pdfContent = $snappy->getOutputFromHtml($html);

        // Retourner le PDF en tant que réponse
        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="reservations.pdf"'
            ]
        );
    }

#[Route('/reservation/{id}/edit', name: 'app_modifier_reservation')]
public function editReservation(int $id, ReservationRepository $repo, Request $request, EntityManagerInterface $em): Response
{
    $reservation = $repo->find($id);

    if (!$reservation) {
        throw $this->createNotFoundException('Réservation non trouvée.');
    }

    if ($request->isMethod('POST')) {
        $reservation->setNomP($request->request->get('nomP'));
        $reservation->setPrenomP($request->request->get('prenomP'));
        $reservation->setEmail($request->request->get('email'));
        $reservation->setNum($request->request->get('num'));
        $reservation->setDateR(new \DateTime($request->request->get('dateR')));
        $reservation->setModeP($request->request->get('modeP'));

        $em->flush();

        return $this->redirectToRoute('app_reservation');
    }

    return $this->render('back/edit_reservation.html.twig', [
        'reservation' => $reservation,
    ]);
}

#[Route('/reservation/{id}/delete', name: 'app_supprimer_reservation', methods: ['POST'])]
public function deleteReservation(int $id, ReservationRepository $repo, EntityManagerInterface $em, Request $request): Response
{
    $reservation = $repo->find($id);

    if (!$reservation) {
        throw $this->createNotFoundException('Réservation non trouvée.');
    }

    if ($this->isCsrfTokenValid('delete' . $reservation->getIdR(), $request->request->get('_token'))) {
        $em->remove($reservation);
        $em->flush();
    }

    return $this->redirectToRoute('app_reservation');
}

#[Route('/competition/ajouter', name: 'app_ajouter_competition')]
public function addCompetition(Request $request, EntityManagerInterface $em): Response
{
    $fieldErrors = [];

    if ($request->isMethod('POST')) {
        $data = $request->request->all();

        // Création du validateur
        $validator = Validation::createValidator();
        $violations = $validator->validate($data, new Assert\Collection([
            'fields' => [
                'nom' => new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                'type' => new Assert\NotBlank(['message' => 'Le type est obligatoire.']),
                'dateC' => [
                    new Assert\NotBlank(['message' => 'La date est obligatoire.']),
                    new Assert\Date(['message' => 'Format de date invalide.']),
                    new Assert\GreaterThanOrEqual([
                        'value' => (new \DateTime())->format('Y-m-d'),
                        'message' => 'La date doit être aujourd\'hui ou dans le futur.',
                    ]),
                ],
                'lieuC' => new Assert\NotBlank(['message' => 'Le lieu est obligatoire.']),
                'nbPart' => [
                    new Assert\NotBlank(['message' => 'Le nombre de participants est obligatoire.']),
                    new Assert\Positive(['message' => 'Le nombre de participants doit être un entier positif.']),
                ],
                'description' => new Assert\NotBlank(['message' => 'La description est obligatoire.']),
            ],
        ]));

        // Gestion des erreurs
        foreach ($violations as $violation) {
            $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
            $fieldErrors[$field][] = $violation->getMessage();
        }

        if (empty($fieldErrors)) {
            // Création et persistance de la compétition
            $competition = new Competition();
            $competition->setNom($data['nom']);
            $competition->setType($data['type']);
            $competition->setDateC(new \DateTime($data['dateC']));
            $competition->setLieuC($data['lieuC']);
            $competition->setNbPart((int)$data['nbPart']);
            $competition->setDescription($data['description']);

            $em->persist($competition);
            $em->flush();

            $this->addFlash('success', 'Compétition ajoutée avec succès !');

            return $this->redirectToRoute('app_competition');
        }
    }

    // Affichage du formulaire avec les erreurs si nécessaire
    return $this->render('back/add_competition.html.twig', [
        'fieldErrors' => $fieldErrors,
    ]);
}

    #[Route('/competitions', name: 'app_competitions')]
    public function listCompetitions(
        Request $request,
        CompetitionRepository $repo,
        PaginatorInterface $paginator
    ): Response {
        $query = $repo->createQueryBuilder('c')
                      ->getQuery();
    
        $competitions = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4
        );
    
        return $this->render('front/index.html.twig', [
            'competitions' => $competitions,
        ]);
    }   
    #[Route('/dashboard', name: 'app_back')]
    public function homeBack(CompetitionRepository $repo, ReservationRepository $reservationRepo): Response
    {
        // Récupérer toutes les compétitions
        $competitions = $repo->findAll();

        // Graphique 1: Nombre de compétitions par mois
        $competitionsByMonth = [];
        foreach ($competitions as $competition) {
            $month = $competition->getDateC()->format('m'); // Extraction du mois (format 'm' pour 01-12)
            if (!isset($competitionsByMonth[$month])) {
                $competitionsByMonth[$month] = 0;
            }
            $competitionsByMonth[$month]++;
        }

        // Graphique 2: Répartition des compétitions par type
        $competitionsByType = [];
        foreach ($competitions as $competition) {
            $type = $competition->getType();
            if (!isset($competitionsByType[$type])) {
                $competitionsByType[$type] = 0;
            }
            $competitionsByType[$type]++;
        }

        // Récupérer toutes les réservations
        $reservations = $reservationRepo->findAll();

        // Graphique 3: Nombre de réservations par mois
        $reservationsByMonth = [];
        foreach ($reservations as $reservation) {
            $month = $reservation->getDateR()->format('m'); // Extraction du mois (format 'm' pour 01-12)
            if (!isset($reservationsByMonth[$month])) {
                $reservationsByMonth[$month] = 0;
            }
            $reservationsByMonth[$month]++;
        }

        // Graphique 4: Répartition des réservations par mode de paiement
        $reservationsByMode = [];
        foreach ($reservations as $reservation) {
            $mode = $reservation->getModeP();
            if (!isset($reservationsByMode[$mode])) {
                $reservationsByMode[$mode] = 0;
            }
            $reservationsByMode[$mode]++;
        }

        // Passer les données à la vue
        return $this->render('back/index.html.twig', [
            'competitionsByMonth' => $competitionsByMonth,
            'competitionsByType' => $competitionsByType,
            'reservationsByMonth' => $reservationsByMonth,
            'reservationsByMode' => $reservationsByMode,
        ]);
    }    
}
