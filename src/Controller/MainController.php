<?php

namespace App\Controller;

use App\Entity\Label;
use App\Entity\Page;
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

    #[Route('/page/{page_id}/{label_id}')]
    public function pageLabel(Page $page, Label $label): Response
    {
        if (false === in_array($page->getId(), $label->printableOn)) {
            throw new \DomainException("Label \"{$label->getId()}\" is not printable on page \"{$page->getId()}\"");
        }

        return $this->render("main/page-label.html.twig", [
            'page' => $page,
            'label' => $label,
        ]);
    }
}
