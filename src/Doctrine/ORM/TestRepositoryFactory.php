<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectRepository;
use Prophecy\Prophecy\ProphecyInterface;
use TypeError;

use function assert;
use function spl_object_hash;

final class TestRepositoryFactory implements RepositoryFactory
{
    /** @var ObjectRepository[] */
    private array $repositoryList = [];

    /**
     * {@inheritDoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName): ObjectRepository
    {
        $repositoryHash = $this->getRepositoryHash($entityManager, $entityName);

        if (isset($this->repositoryList[$repositoryHash])) {
            return $this->repositoryList[$repositoryHash];
        }

        return $this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName);
    }

    /** @param ProphecyInterface|ObjectRepository $repository */
    public function setRepository(EntityManagerInterface $entityManager, string $entityName, object $repository): void
    {
        if ($repository instanceof ProphecyInterface) {
            $repository = $repository->reveal();
        }

        if (! $repository instanceof ObjectRepository) {
            throw new TypeError('Argument 3 passed to ' . __METHOD__ . ' must implement interface ' . ObjectRepository::class . ', instance of ' . $repository::class . ' given.');
        }

        $repositoryHash = $this->getRepositoryHash($entityManager, $entityName);

        $this->repositoryList[$repositoryHash] = $repository;
    }

    private function createRepository(EntityManagerInterface $entityManager, string $entityName): ObjectRepository
    {
        $metadata = $entityManager->getClassMetadata($entityName);
        assert($metadata instanceof ClassMetadata);

        $repositoryClassName = $metadata->customRepositoryClassName ?: $entityManager->getConfiguration()->getDefaultRepositoryClassName();
        $repository = new $repositoryClassName($entityManager, $metadata);

        assert($repository instanceof ObjectRepository);

        return $repository;
    }

    private function getRepositoryHash(EntityManagerInterface $entityManager, string $entityName): string
    {
        return $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);
    }
}
