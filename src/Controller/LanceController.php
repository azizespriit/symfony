<?php

namespace App\Controller;

use App\Repository\ObjectifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LanceController extends AbstractController
{
    #[Route('/front', name: 'app_objectif_front', methods: ['GET'])]
    public function front(ObjectifRepository $objectifRepository): Response
    {
        return $this->render('objectif/front/fnt.html.twig', [
            'objectifs' => $objectifRepository->findAll(),
        ]);
    }
}
