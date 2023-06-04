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

namespace Integration;

use Laudis\Neo4j\ClientBuilder;
use PhpGraphGroup\CypherQueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

class ConnectivityTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $client = ClientBuilder::create()
            ->withDriver('default', $_ENV['CONNECTION'])
            ->build();

        QueryBuilder::attachClient($client);
        QueryBuilder::beginTransaction();
    }

    public function testConnectivity(): void
    {
        $name = QueryBuilder::from('Person')
            ->creating(['name' => 'John'])
            ->returning('name')
            ->only();

        $this->assertEquals('John', $name);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        QueryBuilder::rollbackTransaction();
    }
}
