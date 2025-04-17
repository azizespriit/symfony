<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class ReservationController extends AbstractController
{
    #[Route('/reserver/{id}', name: 'app_reserver_competition', methods: ['GET', 'POST'])]
    public function reserver($id, Request $request, EntityManagerInterface $em)
    {
        $competition = $em->getRepository(Competition::class)->find($id);
        $fieldErrors = [];

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

                $em->persist($reservation);
                $em->flush();

                $this->addFlash('success', 'Réservation effectuée avec succès !');

                return $this->redirectToRoute('app_home', ['id' => $competition->getIdC()]);
            }
        }

        return $this->render('front/reserver.html.twig', [
            'competition' => $competition,
            'fieldErrors' => $fieldErrors,
        ]);
    }
}
