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
         * Merge the label data with the eventual parent ones.
         * Each part of the label's compound id is a parent id.
         */
        foreach ($label->getIdsAllButLast() as $id) {
            /** @var Label */
            $parent = $this->repository->find($id);

            if (null === $parent) {
                throw new \RuntimeException(sprintf('Label "%s" not found.', $id));
            }

            $label->mergeData($parent->getData());
        }

        return $label;
    }
}
