<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\DBAL\Driver\PDO;

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDO\Result;
use Doctrine\DBAL\Driver\PDO\Statement;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\Deprecations\Deprecation;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\DetectsLostConnections;
use PDO;
use PDOException;
use PDOStatement;

class HyperfDatabaseConnection implements ServerInfoAwareConnection
{
    use DetectsLostConnections;

    protected ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function getHyperfConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function getServerVersion()
    {
        return $this->connection->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    public function exec(string $sql): int
    {
        try {
            $result = $this->connection->getPdo()->exec($sql);
        } catch (PDOException $exception) {
            $result = $this->retryIfCausedByLostConnection($exception, function () use ($sql) {
                return $this->connection->getPdo()->exec($sql);
            });
        }

        assert($result !== false);

        return $result;
    }

    public function prepare(string $sql): StatementInterface
    {
        try {
            $stmt = $this->connection->getPdo()->prepare($sql);
        } catch (PDOException $exception) {
            $stmt = $this->retryIfCausedByLostConnection($exception, function () use ($sql) {
                return $this->connection->getPdo()->prepare($sql);
            });
        }

        assert($stmt instanceof PDOStatement);

        return new Statement($stmt);
    }

    public function query(string $sql): ResultInterface
    {
        try {
            $stmt = $this->connection->getPdo()->prepare($sql);
            $stmt->execute();
        } catch (PDOException $exception) {
            $stmt = $this->retryIfCausedByLostConnection($exception, function () use ($sql) {
                $stmt = $this->connection->getPdo()->prepare($sql);
                $stmt->execute();
                return $stmt;
            });
        }

        assert($stmt instanceof PDOStatement);

        return new Result($stmt);
    }

    public function quote($value, $type = ParameterType::STRING)
    {
        return $this->connection->getPdo()->quote($value, $type);
    }

    public function lastInsertId($name = null)
    {
        try {
            if ($name === null) {
                return $this->connection->getPdo()->lastInsertId();
            }

            Deprecation::triggerIfCalledFromOutside(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/issues/4687',
                'The usage of Connection::lastInsertId() with a sequence name is deprecated.',
            );

            return $this->connection->getPdo()->lastInsertId($name);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function beginTransaction(): bool
    {
        try {
            $this->connection->getPdo()->beginTransaction();
        } catch (PDOException $exception) {
            $this->retryIfCausedByLostConnection($exception, function () {
                $this->connection->getPdo()->beginTransaction();
            });
        }

        return true;
    }

    public function commit(): bool
    {
        $this->connection->getPdo()->commit();
        return true;
    }

    public function rollBack(): bool
    {
        try {
            $this->connection->getPdo()->rollBack();
        } catch (PDOException $exception) {
            $this->retryIfCausedByLostConnection($exception, function () {
                $this->connection->getPdo()->rollBack();
            });
        }

        return true;
    }

    /**
     * This method is used to retry a database operation if the failure was caused by a lost connection.
     */
    private function retryIfCausedByLostConnection(\Exception $exception, callable $callback): mixed
    {
        if (! $this->causedByLostConnection($exception)) {
            if ($exception instanceof PDOException) {
                throw Exception::new($exception);
            }

            throw $exception;
        }

        try {
            $this->connection->reconnect();
            return $callback();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }
}
