<?php

namespace App\Factory;

use App\Entity\Label;
use App\Repository\LabelRepository;

class LabelFactory implements ObjectFactoryInterface
{
    public function __construct(protected LabelRepository $repository)
    {
    }

    public function createFromArray(string $id, array $data): Label
    {
        $label = new Label($id, $data);

        // Merge the label data with that of a possible parent.
        if ($parentId = $label->getParentId()) {
            $parent = $this->repository->find($parentId);

            if (null !== $parent) {
                $label->mergeData($parent->getData(), false);
            }
        }

        return $label;
    }
}
