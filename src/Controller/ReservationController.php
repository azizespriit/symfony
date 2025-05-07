<?php
namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Reservation;
use App\Repository\CompetitionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use App\Service\MailjetService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;  // Importer SessionInterface
use App\Service\GeocodingService;

class ReservationController extends AbstractController
{
    private $geocodingService;
    
    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;  // Initialisez-le dans le constructeur
    }

    #[Route('/front/évennement', name: 'app_evennement_list')]
    public function listCompetitions(CompetitionRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $repo->createQueryBuilder('c'); // Créer le QueryBuilder
        $competitionsQuery = $queryBuilder->getQuery(); // Créer la requête

        // Pagination
        $competitions = $paginator->paginate(
            $competitionsQuery, // La requête à paginer
            $request->query->getInt('page', 1), // Page actuelle (par défaut 1)
            5 // Nombre d'éléments par page
        );
        
        return $this->render('front/evennementsList.html.twig', [
            'competitions' => $competitions,
        ]);
    }
   
    #[Route('/reserver/{id}', name: 'app_reserver_competition', methods: ['GET', 'POST'])]
    public function reserver($id, Request $request, EntityManagerInterface $em, MailjetService $mailjetService, SessionInterface $session)  // Injecter SessionInterface ici
    {
        $competition = $em->getRepository(Competition::class)->find($id);
        $fieldErrors = [];

        // Utiliser le service de géocodage pour récupérer les coordonnées
        $coordinates = null;
        if ($competition->getLieuC()) {
        $coordinates = $this->geocodingService->getCoordinatesFromPlace($competition->getLieuC());
    }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $validator = Validation::createValidator();
            $violations = $validator->validate($data, new Assert\Collection([
                'fields' => [
                    'nomP' => [
                        new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                        new Assert\Length([
                            'min' => 2,
                            'max' => 30,
                            'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                            'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/u',
                            'message' => 'Le nom ne doit contenir que des lettres.',
                        ]),
                    ],
                    'prenomP' => [
                        new Assert\NotBlank(['message' => 'Le prénom est obligatoire.']),
                        new Assert\Length([
                            'min' => 2,
                            'max' => 30,
                            'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                            'maxMessage' => 'Le prénom ne doit pas dépasser {{ limit }} caractères.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/u',
                            'message' => 'Le prénom ne doit contenir que des lettres.',
                        ]),
                    ],
                    'email' => [
                        new Assert\NotBlank(['message' => 'L\'email est obligatoire.']),
                        new Assert\Email(['message' => 'L\'email n\'est pas valide.']),
                        new Assert\Length([
                            'max' => 100,
                            'maxMessage' => 'L\'email ne doit pas dépasser {{ limit }} caractères.',
                        ]),
                    ],
                    'num' => [
                        new Assert\NotBlank(['message' => 'Le numéro est obligatoire.']),
                        new Assert\Regex([
                            'pattern' => '/^[0-9]{8,15}$/',
                            'message' => 'Le numéro doit contenir entre 8 et 15 chiffres.',
                        ]),
                    ],
                    'dateR' => [
                        new Assert\NotBlank(['message' => 'La date est obligatoire.']),
                        new Assert\Date(['message' => 'La date doit être au format valide.']),
                        new Assert\GreaterThanOrEqual([
                            'value' => (new \DateTime())->format('Y-m-d'),
                            'message' => 'La date de réservation doit être aujourd\'hui ou dans le futur.',
                        ]),
                    ],
                    'modeP' => [
                        new Assert\NotBlank(['message' => 'Le mode de paiement est obligatoire.']),
                        new Assert\Choice([
                            'choices' => ['En ligne', 'Carte', 'Espèce'],
                            'message' => 'Le mode de paiement sélectionné est invalide.',
                        ]),
                    ],
                ],
                'allowExtraFields' => true
            ]));

            foreach ($violations as $violation) {
                $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
                $fieldErrors[$field][] = $violation->getMessage();
            }

            if (empty($fieldErrors)) {
                $reservation = new Reservation();
                $reservation->setNomP($data['nomP']);
                $reservation->setPrenomP($data['prenomP']);
                $reservation->setEmail($data['email']);
                $reservation->setNum((int)$data['num']);
                $reservation->setDateR(new \DateTime($data['dateR']));
                $reservation->setModeP($data['modeP']);
                $reservation->setCompetition($competition);

                // Générer le code de confirmation
                $codeConfirmation = random_int(100000, 999999);
                $reservation->setCodeConfirmation($codeConfirmation);

                // Sauvegarder la réservation en base de données
                $em->persist($reservation);
                $em->flush();

                // Enregistrer le code de confirmation dans la session
                $session->set('confirmation_code', $codeConfirmation);  // Utilisation de la session ici

                // Envoyer l'email de confirmation
                $mailjetService->sendConfirmationEmail(
                    $reservation->getEmail(),
                    $reservation->getNomP(),
                    $codeConfirmation
                );

                $this->addFlash('success', 'Réservation effectuée avec succès !');

                // Rediriger l'utilisateur vers une page où il peut entrer le code de confirmation
                return $this->redirectToRoute('app_confirm_code');
            }
        }

        return $this->render('front/reserver.html.twig', [
            'competition' => $competition,
            'fieldErrors' => $fieldErrors,
            'latitude' => $coordinates['latitude'] ?? null,  // Passer la latitude à Twig
            'longitude' => $coordinates['longitude'] ?? null,  // Passer la longitude à Twig
        ]);
    }

    #[Route('/confirm-code', name: 'app_confirm_code', methods: ['GET', 'POST'])]
    public function confirmCode(Request $request, SessionInterface $session)  // Injection de SessionInterface ici
    {
        // Récupérer le code de confirmation stocké dans la session
        $sessionCode = $session->get('confirmation_code');  // Utilisation de la session ici

        // Si aucun code n'est stocké, rediriger vers la page d'accueil
        if (!$sessionCode) {
            return $this->redirectToRoute('app_home');
        }

        // Gérer le formulaire de confirmation
        if ($request->isMethod('POST')) {
            $submittedCode = $request->request->get('confirmation_code');

            if ($submittedCode == $sessionCode) {
                // Le code est valide
                $this->addFlash('success', 'Votre réservation a été confirmée avec succès !');

                // Optionnel : supprimer le code de la session après confirmation
                $session->remove('confirmation_code');  // Utilisation de la session ici

                return $this->redirectToRoute('app_evennement_list');
            } else {
                // Le code est incorrect
                $this->addFlash('error', 'Le code de confirmation est incorrect. Veuillez réessayer.');
            }
        }

        return $this->render('front/confirm_code.html.twig');
    }
}
