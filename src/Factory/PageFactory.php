<?php

namespace App\Factory;

use App\Entity\Page;
use App\Repository\PageRepository;

class PageFactory implements ObjectFactoryInterface
{
    public function __construct(protected PageRepository $repository)
    {
    }

    public function createFromArray(string $id, array $data): Page
    {
        $page = new Page($id, $data);

        return $page;
    }
}
