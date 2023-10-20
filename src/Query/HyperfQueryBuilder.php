<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Builder as BaseBuilder;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;
use InvalidArgumentException;
use RuntimeException;

class HyperfQueryBuilder extends BaseBuilder
{
    /**
     * The maximum number of results to retrieve.
     */
    private ?int $maxResults = null;

    /**
     * The index of the first result to retrieve.
     */
    private int $firstResult = 0;

    /**
     * The hydration mode to use.
     */
    private int $hydrationMode = Query::HYDRATE_OBJECT;

    public function __construct(
        protected EntityManagerInterface $em,
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null,
    ) {
        parent::__construct($connection, $grammar, $processor);
    }

    /**
     * Set the result format mode.
     * Can be any of the hydration modes supported by Doctrine.
     * @see \Doctrine\ORM\AbstractQuery::HYDRATE_*
     * @return $this
     */
    public function setHydrationMode(int $hydrationMode): HyperfQueryBuilder
    {
        $this->hydrationMode = $hydrationMode;
        return $this;
    }

    /**
     * Alias must be set in DQL.
     *
     * @param mixed $table Entity class name
     * @return HyperfQueryBuilder
     */
    public function from($table, string $alias = null)
    {
        return parent::from($this->alias($table, $alias));
    }

    public function alias($table, string $alias = null): string
    {
        if (empty($alias)) {
            throw new InvalidArgumentException('Table alias must be set in DQL.');
        }

        return $table . ' ' . $alias;
    }

    public function count($columns = '*')
    {
        if ($columns == '*') {
            throw new InvalidArgumentException('Not support `*` in count. Specify the entity property instead.');
        }

        return parent::count($columns);
    }

    public function limit($value)
    {
        $value >= 0 && $this->maxResults = $value;

        return $this;
    }

    public function offset($value)
    {
        $this->firstResult = max(0, $value);
        return $this;
    }

    public function exists()
    {
        throw new RuntimeException('Not support exists() in DQL. Use native SQL instead.');
    }

    public function getDQL(): string
    {
        return $this->toSql();
    }

    public function newQuery()
    {
        return new static($this->em, $this->connection, $this->grammar, $this->processor);
    }

    public function update(array $values)
    {
        $sql = $this->grammar->compileUpdate($this, $values);
        $query = $this->em->createQuery($sql);
        $this->buildParameters($query, $this->cleanBindings($this->grammar->prepareBindingsForUpdate($this->bindings, $values)));

        return $query->execute();
    }

    public function delete($id = null)
    {
        if (! is_null($id)) {
            $this->where($this->from . '.id', '=', $id);
        }

        $query = $this->em->createQuery($this->grammar->compileDelete($this));
        $this->buildParameters($query, $this->cleanBindings($this->grammar->prepareBindingsForDelete($this->bindings)));

        return $query->execute();
    }

    protected function buildParameters(Query $query, array $bindings): void
    {
        $key = 0;
        foreach ($bindings as $binding) {
            $query->setParameter($key, $binding);
            ++$key;
        }
    }

    protected function runSelect()
    {
        $query = $this->em->createQuery($this->getDQL());
        $query
            ->setFirstResult($this->firstResult)
            ->setMaxResults($this->maxResults);
        $this->buildParameters($query, $this->connection->prepareBindings($this->getBindings()));

        return $query->getResult($this->hydrationMode);
    }
}
