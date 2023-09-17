<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    protected static array $labels = [
        'terrine' => [
            'title' => 'Terrine de Chevreau',
            'weight' => '180g',
            'ingredients' => ['viande 100% chevreau', 'sel', 'poivre'],
            'nature' => [
                'ingredients_extra' => ['origan'],
            ],
        ]
    ];

    #[Route('/label/{id}')]
    public function label(string $id): Response
    {
        return $this->render("main/label.html.twig", [
            'template' => $this->getLabelTemplateById($id),
            'label' => $this->getLabelDataById($id),
        ]);
    }

    protected function getLabelTemplateById(string $id): string
    {
        return strtok($id, '-');
    }

    protected function getLabelDataById(string $id): array
    {
        $labels = & self::$labels;

        $data = [
            'ids' => [], // decomposed label (eg: 'my.label.two' => ['my', 'label', 'two'])
        ];

        $id = strtok($id, '-');

        do {
            if (! array_key_exists($id, $labels)) {
                break;
            }

            $labels = & $labels[$id];
            $data['ids'][] = $id;
            $data = array_merge($data, $labels);
            $id = strtok('-');
        } while (false !== $id);

        return $data;
    }
}
