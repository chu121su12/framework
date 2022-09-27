<?php

namespace Illuminate\Database\DBAL;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MariaDb1027Platform;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQL57Platform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServer2012Platform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Types\PhpDateTimeMappingType;
use Doctrine\DBAL\Types\Type;

class TimestampTypeBase extends Type/* implements PhpDateTimeMappingType*/
{
    /**
     * {@inheritdoc}
     *
     * @throws DBALException
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)/*: string*/
    {
        switch (get_class($platform)) {
            case MySqlPlatform::class:
            case MySQL57Platform::class:
            // case MySQL80Platform::class:
            // case MariaDBPlatform::class:
            // case MariaDb1027Platform::class:
                return $this->getMySqlPlatformSQLDeclaration($column);

            case PostgreSQLPlatform::class:
            // case PostgreSQL94Platform::class:
            // case PostgreSQL100Platform::class:
                return $this->getPostgresPlatformSQLDeclaration($column);

            case SQLServerPlatform::class:
            case SQLServer2012Platform::class:
                return $this->getSqlServerPlatformSQLDeclaration($column);

            case SqlitePlatform::class:
                return $this->getSQLitePlatformSQLDeclaration($column);
        }

        switch ($name = $platform->getName()) {
            case 'mysql':
            case 'mysql2': return $this->getMySqlPlatformSQLDeclaration($column);
            case 'postgresql':
            case 'pgsql':
            case 'postgres': return $this->getPostgresPlatformSQLDeclaration($column);
            case 'mssql': return $this->getSqlServerPlatformSQLDeclaration($column);
            case 'sqlite':
            case 'sqlite3': return $this->getSQLitePlatformSQLDeclaration($column);
        }

        throw new DBALException('Invalid platform: '.substr(strrchr(get_class($platform), '\\'), 1));
    }

    /**
     * Get the SQL declaration for MySQL.
     *
     * @param  array  $column
     * @return string
     */
    protected function getMySqlPlatformSQLDeclaration(array $column)/*: string*/
    {
        $columnType = 'TIMESTAMP';

        if ($column['precision']) {
            $columnType = 'TIMESTAMP('.min((int) $column['precision'], 6).')';
        }

        $notNull = isset($column['notnull']) ? $column['notnull'] : false;

        if (! $notNull) {
            return $columnType.' NULL';
        }

        return $columnType;
    }

    /**
     * Get the SQL declaration for PostgreSQL.
     *
     * @param  array  $column
     * @return string
     */
    protected function getPostgresPlatformSQLDeclaration(array $column)/*: string*/
    {
        return 'TIMESTAMP('.min((int) $column['precision'], 6).')';
    }

    /**
     * Get the SQL declaration for SQL Server.
     *

     * @return string
     */
    protected function getSqlServerPlatformSQLDeclaration(array $column)/*: string*/
    {
        return (isset($column['precision']) ? $column['precision'] : false)
            ? 'DATETIME2('.min((int) $column['precision'], 7).')'
            : 'DATETIME';
    }

    /**
     * Get the SQL declaration for SQLite.
     *
     * @param  array  $fieldDeclaration
     * @return string
     */
    protected function getSQLitePlatformSQLDeclaration(array $column)/*: string*/
    {
        return 'DATETIME';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'timestamp';
    }
}

if (interface_exists(PhpDateTimeMappingType::class))
{
    class TimestampType extends TimestampTypeBase implements PhpDateTimeMappingType
    {
    }
}
else
{
    class TimestampType extends TimestampTypeBase
    {
    }
}
