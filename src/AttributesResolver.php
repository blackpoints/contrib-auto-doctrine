<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Instrumentation\Doctrine;

use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Exception;

final class AttributesResolver
{
    /**
     * See list of well-known values at https://opentelemetry.io/docs/specs/semconv/attributes-registry/db/
     */
    private const DB_SYSTEMS_KNOWN = [
        SQLServerPlatform::class => 'mssql',
        MariaDBPlatform::class => 'mariadb',
        MySQLPlatform::class => 'mysql',
        OraclePlatform::class => 'oracle',
        DB2Platform::class => 'db2',
        PostgreSQLPlatform::class => 'postgresql',
        SqlitePlatform::class => 'sqlite',
    ];

    public static function get(string $attributeName, array $params)
    {
        $method = 'get' . str_replace('.', '', ucwords($attributeName, '.'));

        if (!method_exists(AttributesResolver::class, $method)) {
            throw new Exception(sprintf('Attribute %s not supported by Doctrine', $attributeName));
        }
        return self::{$method}($params);
    }

    /**
     * Resolve attribute `server.address`
     */
    private static function getServerAddress(array $params): string
    {
        return $params[1][0]['host'] ?? 'unknown';
    }

    /**
     * Resolve attribute `server.port`
     */
    private static function getServerPort(array $params): string
    {
        return $params[1][0]['port'] ?? 'unknown';
    }

    /**
     * Resolve attribute `db.system`
     */
    private static function getDbSystem(array $params): string
    {
        $dbSystem = $params[1][0]['driver'] ?? null;

        if ($dbSystem) {
            $doctrineDriver = $params[0];
            foreach (self::DB_SYSTEMS_KNOWN as $platform => $system) {
                if (is_subclass_of($doctrineDriver->getDatabasePlatform(), $platform)) {
                    $dbSystem = $system;
                    break;
                }
            }
        }
        return $dbSystem ?? 'other_sql';
    }

    /**
     * Resolve attribute `db.collection.name`
     */
    private static function getDbCollectionName(array $params): string
    {
        return $params[1][0]['dbname'] ?? 'unknown';
    }

    /**
     * Resolve attribute `db.query.text`
     * No sanitization is implemented because implicitly the query is expected to be expressed as a preparated statement
     */
    private static function getDbQueryText(array $params): string
    {
        return $params[1][0] ?? 'undefined';
    }

    /**
     * Resolve attribute `db.operation.name`
     */
    private static function getDbOperationName(array $params): string
    {
    }

    private static function getDbNamespace(array $params): string
    {
        return $params[1][0]['dbname'] ?? 'unknown';
    }
}
