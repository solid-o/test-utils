<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Repository\RepositoryFactory;
use Prophecy\Prophecy\ProphecyInterface;
use TypeError;

use function spl_object_hash;

final class TestRepositoryFactory implements RepositoryFactory
{
    /** @var array<string, EntityRepository<object>> */
    private array $repositoryList = [];

    /** @return EntityRepository<object> */
    public function getRepository(EntityManagerInterface $entityManager, string $entityName): EntityRepository
    {
        $repositoryHash = $this->getRepositoryHash($entityManager, $entityName);

        if (isset($this->repositoryList[$repositoryHash])) {
            return $this->repositoryList[$repositoryHash];
        }

        return $this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName);
    }

    /** @param ProphecyInterface<EntityRepository<object>>|EntityRepository<object> $repository */
    public function setRepository(EntityManagerInterface $entityManager, string $entityName, object $repository): void
    {
        if ($repository instanceof ProphecyInterface) {
            $repository = $repository->reveal();
        }

        if (! $repository instanceof EntityRepository) {
            throw new TypeError('Argument 3 passed to ' . __METHOD__ . ' must implement class ' . EntityRepository::class . ', instance of ' . $repository::class . ' given.');
        }

        $repositoryHash = $this->getRepositoryHash($entityManager, $entityName);

        $this->repositoryList[$repositoryHash] = $repository;
    }

    /** @return EntityRepository<object> */
    private function createRepository(EntityManagerInterface $entityManager, string $entityName): EntityRepository
    {
        $metadata = $entityManager->getClassMetadata($entityName);

        $repositoryClassName = $metadata->customRepositoryClassName ?: $entityManager->getConfiguration()->getDefaultRepositoryClassName();

        return new $repositoryClassName($entityManager, $metadata);
    }

    private function getRepositoryHash(EntityManagerInterface $entityManager, string $entityName): string
    {
        return $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);
    }
}
