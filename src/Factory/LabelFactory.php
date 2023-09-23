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

        /**
         * Merge the label data with an eventual parent one
         * (the parent ID is the first ID part).
         */
        if ($parentId = $label->getParentId()) {
            $parent = $this->repository->find($parentId);

            if (null === $parent) {
                throw new \RuntimeException(sprintf('Parent label "%s" of "%s" not found.', $parentId, $id));
            }

            $label->mergeData($parent->getData());
        }

        return $label;
    }
}
