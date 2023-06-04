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

namespace PhpGraphGroup\CypherQueryBuilder\Concerns\Builder;

use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\SummarizedResult;
use RuntimeException;

/**
 * @implements \PhpGraphGroup\CypherQueryBuilder\Contracts\HasStaticClient
 */
trait HasStaticClient
{
    private static ClientInterface|null $client = null;

    private string|null $connection = null;

    public function usingConnection(string|null $alias): static
    {
        $this->connection = $alias;

        return $this;
    }

    public static function beginTransaction(string|null $connection = null): void
    {
        self::getClient()->bindTransaction($connection);
    }

    private static function getClient(): ClientInterface
    {
        if (self::$client === null) {
            throw new RuntimeException('No client attached');
        }

        return self::$client;
    }

    /**
     * Connect a client to the builder, so it can actually run queries.
     */
    public static function attachClient(ClientInterface $client): void
    {
        self::$client = $client;
    }

    public static function commitTransaction(string|null $connection = null, int $depth = 1): void
    {
        self::getClient()->commitBoundTransaction($connection);
    }

    public static function rollbackTransaction(string|null $connection = null, int $depth = 1): void
    {
        self::getClient()->commitBoundTransaction($connection, $depth);
    }

    /**
     * @param iterable<string, mixed> $parameters
     */
    private function run(string $query, iterable $parameters = []): SummarizedResult
    {
        return self::getClient()->run($query, $parameters, $this->connection);
    }
}
