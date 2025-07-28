<?php

namespace App\Entity\Trait;

/**
 * Implements a way to store data in bulk, as an array, with versioning support.
 */
trait VersionedDataTrait
{
    use DataTrait;

    /**
     * Load extra data over the original ones from version IDs.
     *
     * @param array $versions IDs of the versions to load.

     * @return array Return an array of the IDs of actually loaded versions.
     *
     * @throws \DomainException If a version is not found and $strict is true.
     */
    public function loadVersions(array $versions): array
    {
        $loaded = [];

        foreach ($versions as $id) {
            $version = $this->versions[$id] ?? null;

            if (null === $version) {
                continue;
            }

            $this->mergeData($version, true);

            $loaded[] = $id;
        }

        return $loaded;
    }
}