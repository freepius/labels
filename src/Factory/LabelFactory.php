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
                $label->parent = $parentId;
                $label->mergeData($parent->getData(), false);
            }
        }

        if (empty($label->versions)) {
            // If no versions are defined, we can skip the rest.
            return $label;
        }

        // Copy the versions data when they are defined outside of the label data, via an alias (e.g., "v#my-version-id").
        $versions = [];
        foreach ($label->versions as $id => $version) {
            if (is_int($id) && is_string($version)) {
                $data = $this->repository->find("v#{$version}")?->getData();

                if (null === $data) {
                    throw new \DomainException("Version \"{$version}\" not found for label \"{$label->getId()}\"");
                } else {
                    $versions[$version] = $data;
                }
            } else {
                $versions[$id] = $version;
            }
        }

        // Process the versions through the factory to ensure they are properly initialized.
        foreach ($versions as $id => $version) {
            $versions[$id] = $this->createFromArray($id, $version)?->getData();
        }
        $label->versions = $versions;

        return $label;
    }
}
