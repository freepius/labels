<?php

namespace App\Factory;

interface ObjectFactoryInterface
{
    /**
     * Creates an object from an array of data.
     *
     * @param string  $id    The object identifier.
     * @param array   $data  The object data.
     *
     * @return object
     */
    public function createFromArray(string $id, array $data): object;
}
