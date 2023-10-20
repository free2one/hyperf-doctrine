<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Query\Grammars;

use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Grammars\MySqlGrammar;
use Hyperf\Database\Query\JoinClause;
use RuntimeException;

class DqlGrammar extends MySqlGrammar
{
    protected int $parameterOffset = 0;

    public function compileSelect(Builder $query): string
    {
        $this->resetParameterOffset();
        return parent::compileSelect($query);
    }

    public function compileInsert(Builder $query, array $values): string
    {
        throw new RuntimeException('INSERT statements are not allowed in DQL.Use `EntityManager#persist()` instead.');
    }

    public function compileRandom($seed): string
    {
        throw new RuntimeException('RAND() is not allowed in DQL. Use native SQL instead.');
    }

    public function parameter(mixed $value)
    {
        $parameter = parent::parameter($value);
        if ($parameter == '?') {
            $parameter .= $this->parameterOffset;
            ++$this->parameterOffset;
        }

        return $parameter;
    }

    protected function compileLock(Builder $query, $value): string
    {
        throw new RuntimeException('lock is not allowed in DQL. Use `EntityManager#lock()` instead.`');
    }

    protected function resetParameterOffset(): void
    {
        $this->parameterOffset = 0;
    }

    protected function wrapValue($value): string
    {
        return $value;
    }

    /**
     * use `with` instead of `on` in DQL.
     * @param mixed $query
     * @param mixed $sql
     */
    protected function concatenateWhereClauses($query, $sql): string
    {
        $conjunction = $query instanceof JoinClause ? 'with' : 'where';

        return $conjunction . ' ' . $this->removeLeadingBoolean(implode(' ', $sql));
    }
}
