<?php

namespace App\Controller;

use App\Entity\Label;
use App\Repository\LabelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/label/{id}')]
    public function label(Label $label, LabelRepository $labelRepository): Response
    {
        return $this->render("main/label.html.twig", [
            'label' => $label,
            'labels' => $labelRepository->findAll(),
        ]);
    }
}
