<?php

namespace App\Repository;

use App\Factory\ObjectFactoryInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for all repositories
 * whose objects are expected to be stored in YAML files.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * The objects in a PHP representation.
     */
    protected ?array $objects = null;

    /**
     * The Symfony Finder instance used to find data files.
     */
    protected Finder $finder;

    /**
     * @param string                $dataPath   Path to the data directory.
     * @param string|string[]|null  $dataFiles  Data file name(s) where objects are stored.
     *                              If not provided, a default value is guessed.
     *
     * @see \Symfony\Component\Finder\Finder::name() for the $dataFiles format.
     */
    public function __construct(string $dataPath, string|array|null $dataFiles = null)
    {
        $dataFiles ??= $this->guessDataFiles();

        $this->finder = Finder::create()->in($dataPath)->files()->name($dataFiles);

        if (false === $this->finder->hasResults()) {
            throw new \InvalidArgumentException(
                sprintf('No data file exists or is readable (path: "%s", files: "%s").', $dataPath, $dataFiles)
            );
        }
    }

    /**
     * Guess the data file name(s).
     * Eg: `UserRepository` becomes `['user.yaml', 'user/*.yaml']`.
     */
    protected function guessDataFiles(): string|array
    {
        $name = strtolower($this->getClassName());

        return ["$name.yaml", "$name/*.yaml"];
    }

    /**
     * Get the factory FQCN allowing to create objects.
     */
    protected function getFactoryFQCN(): string
    {
        return "\\App\\Factory\\{$this->getClassName()}Factory";
    }

    /**
     * Get the factory instance allowing to create objects.
     */
    protected function getFactory(): ObjectFactoryInterface
    {
        static $factory = null;

        if (null === $factory) {
            $class = $this->getFactoryFQCN();
            $factory = new $class($this);
        }

        return $factory;
    }

    /**
     * Fetch all data from the data files
     * and transform/process them into objects.
     */
    protected function fetchAll(): void
    {
        $this->objects = [];

        foreach ($this->finder as $file) {
            $data = Yaml::parseFile($file->getRealPath());

            if (null === $data) {
                throw new \RuntimeException(
                    sprintf('Unable to parse data file "%s".', $file->getRealPath())
                );
            }

            // At this point, we just accumulate the raw data.
            $this->objects = array_merge($this->objects, $data);
        }

        // Here, we transform/process the raw data into objects.
        foreach ($this->objects as $id => &$object) {
            $object = $this->getFactory()->createFromArray($id, $object);
        }
    }

    /**
     * Get all objects (fetching them if needed).
     */
    protected function all(): array
    {
        if (null === $this->objects) {
            $this->fetchAll();
        }

        return $this->objects;
    }

    /*****
     * API
     *****/

    public function getClassName(): string
    {
        return preg_replace('/Repository$/', '', (new \ReflectionClass($this))->getShortName());
    }

    public function findAll(): array
    {
        return $this->all();
    }

    public function exists(mixed $id): bool
    {
        return isset($this->all()[$id]);
    }

    public function find(mixed $id): ?object
    {
        return $this->all()[$id] ?? null;
    }
}
