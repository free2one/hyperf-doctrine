<?php

declare(strict_types=1);

namespace HyperfTest\Tools;

use Hyperf\Doctrine\EntityManagerFactory;

/**
 * @internal
 */
class DataTestHelper
{
    public static function initTableDataAndReturn($filename, string $entityClass): array
    {
        $insertedData = $tmp = [];
        $em = EntityManagerFactory::getManager();
        $em->clear();

        $userTableData = include 'tests/Mock/TableData/' . $filename . '.php';
        foreach ($userTableData['data'] as $row) {
            $em->getConnection()->insert($userTableData['table'], $row);
        }
        $persister = $em->getUnitOfWork()->getEntityPersister($entityClass);
        foreach ($persister->loadAll() as $entity) {
            $insertedData[$entity->getId()] = $entity;
        }
        $em->clear();

        return $insertedData;
    }

    public static function truncateTable($table): void
    {
        EntityManagerFactory::getManager()->getConnection()->executeStatement('truncate ' . $table);
    }
}
