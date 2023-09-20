<?php

namespace App\Entity;

class Label
{
    use Trait\CompoundIdTrait;
    use Trait\DataTrait;

    public function __construct(string $id, array $data)
    {
        $this->setId($id)->setData($data);
    }

    public function getTemplateName(): string
    {
        return sprintf('label/%s.html.twig', $this->getIds()[0]);
    }
}
