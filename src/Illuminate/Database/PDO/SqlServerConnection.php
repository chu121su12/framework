<?php

namespace Illuminate\Database\PDO;

use Doctrine\DBAL\Driver\PDO\SQLSrv\Statement;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use PDO;

class SqlServerConnection implements ServerInfoAwareConnection
{
    /**
     * The underlying connection instance.
     *
     * @var \Illuminate\Database\PDO\Connection
     */
    protected $connection;

    /**
     * Create a new SQL Server connection instance.
     *
     * @param  \Illuminate\Database\PDO\Connection  $connection
     * @return void
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Prepare a new SQL statement.
     *
     * @param  string  $sql
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function prepare(/*string */$sql)/*: StatementInterface*/
    {
        $sql = backport_type_check('string', $sql);

        return new Statement(
            $this->connection->prepare($sql)
        );
    }

    /**
     * Execute a new query against the connection.
     *
     * @param  string  $sql
     * @return \Doctrine\DBAL\Driver\Result
     */
    public function query(/*string */$sql)/*: Result*/
    {
        $sql = backport_type_check('string', $sql);

        return $this->connection->query($sql);
    }

    /**
     * Execute an SQL statement.
     *
     * @param  string  $statement
     * @return int
     */
    public function exec(/*string */$statement)/*: int*/
    {
        $statement = backport_type_check('string', $statement);

        return $this->connection->exec($statement);
    }

    /**
     * Get the last insert ID.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function lastInsertId($name = null)
    {
        if ($name === null) {
            return $this->connection->lastInsertId($name);
        }

        return $this->prepare('SELECT CONVERT(VARCHAR(MAX), current_value) FROM sys.sequences WHERE name = ?')
            ->execute([$name])
            ->fetchOne();
    }

    /**
     * Begin a new database transaction.
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a database transaction.
     *
     * @return bool
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rollback a database transaction.
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * Wrap quotes around the given input.
     *
     * @param  string  $value
     * @param  int  $type
     * @return string
     */
    public function quote($value, $type = ParameterType::STRING)
    {
        $val = $this->connection->quote($value, $type);

        // Fix for a driver version terminating all values with null byte...
        if (\is_string($val) && str_contains($val, "\0")) {
            $val = \substr($val, 0, -1);
        }

        return $val;
    }

    /**
     * Get the server version for the connection.
     *
     * @return string
     */
    public function getServerVersion()
    {
        return $this->connection->getServerVersion();
    }

    /**
     * Get the wrapped PDO connection.
     *
     * @return \PDO
     */
    public function getWrappedConnection()/*: PDO*/
    {
        return $this->connection->getWrappedConnection();
    }
}
