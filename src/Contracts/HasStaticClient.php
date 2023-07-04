<?php

declare(strict_types=1);

/*
 * This file is part of the PHP Graph Group Query Builder Package.
 *
 * (c) Nagels <https://nagels.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGraphGroup\CypherQueryBuilder\Contracts;

use Laudis\Neo4j\Contracts\ClientInterface;

interface HasStaticClient
{
    /**
     * Connect a client to the builder, so it can actually run queries.
     */
    public static function attachClient(ClientInterface $client): void;

    /**
     * Use a different connection from the default option for this instance.
     *
     * @return $this
     */
    public function usingConnection(string|null $alias): static;

    /**
     * Begin a transaction over this connection.
     *
     * @param string|null $connection The connection to begin a transaction over. Null means the default connection.
     */
    public static function beginTransaction(string $connection = null): void;

    /**
     * Commit a transaction over this connection.
     *
     * @param string|null $connection The connection to commit a transaction over. Null means the default connection.
     */
    public static function commitTransaction(string $connection = null): void;

    /**
     * Rollback a transaction over this connection.
     *
     * @param string|null $connection The connection to roll back a transaction over. Null means the default connection.
     */
    public static function rollbackTransaction(string $connection = null): void;
}
