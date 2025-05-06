<?php

namespace App\Controller;
use App\Entity\Jour;
use App\Repository\JourRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class JourController extends AbstractController
{
    #[Route('/api/jours/{idObjectif}', name: 'api_jours_par_objectif', methods: ['GET'])]
    public function getJoursParObjectif(int $idObjectif, JourRepository $jourRepository): JsonResponse
    {
        $jours = $jourRepository->findByObjectifId($idObjectif);

        $data = [];

        foreach ($jours as $jour) {
            $data[] = [
                'id' => $jour->getId(),
                'nom' => $jour->getNom(),
                'id_objectif' => $jour->getObjectif()->getId()
            ];
        }

        return $this->json($data);
    }
}
