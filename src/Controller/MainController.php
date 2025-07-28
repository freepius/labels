<?php

namespace App\Controller;

use App\Entity\Label;
use App\Entity\Page;
use App\Repository\LabelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/label/{id}')]
    public function label(Label $label, LabelRepository $labelRepository, Request $request): Response
    {
        $label->loadVersions($this->getVersionsQuery($request));

        return $this->render("main/label.html.twig", [
            'label' => $label,
            'labels' => $labelRepository->findAll(),
        ]);
    }

    #[Route('/page/{id}')]
    public function page(Page $page, LabelRepository $labelRepository): Response
    {
        return $this->render("main/page.html.twig", [
            'page' => $page,
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
    protected function getVersionsQuery(Request $request): array
    {
        $query = (string) $request->query->get('v', '');
        $query = explode(',', $query); // Split by commas
        $query = array_map('trim', $query); // Trim spaces
        $query = array_filter($query, fn($item) => !empty($item)); // Remove empty items

        return $query;
    }
}
