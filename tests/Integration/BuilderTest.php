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

use DateTime;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder;
use PhpGraphGroup\CypherQueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private function assertEqualCypher(string $expected, string $actual): void
    {
        $expected = str_replace("\n", ' ', $expected);
        $expected = preg_replace('/\s+/', ' ', $expected);
        $expected = trim($expected);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateRelationship(): void
    {
        $cypher = QueryBuilder::fromNode('Origin')
            ->matchingNode('Origin', name: 'origin2')
            ->whereIn('origin.name', ['one', 'two'])
            ->whereIn('origin2.name', ['three', 'four'])
            ->creatingConnection('origin', 'CONNECTION', 'origin2')
            ->setting('connection.test', 'abc')
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (origin:Origin),(origin2:Origin)
        WHERE origin.name IN $param0 AND origin2.name IN $param1
        CREATE (origin)-[connection:CONNECTION]->(origin2)
        SET connection.test = $param2
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testFull(): void
    {
        $cypher = QueryBuilder::from('Test')
            ->matchingRelationship('test', 'GOES_TO', 'otherTest')
            ->matchingNode('OtherTest')
            ->calling(static function (Builder\SubQueryBuilder $q) {
                $q->matchingNode('XO', 'x')
                    ->matchingRelationship('otherTest', 'Hello', 'x')
                    ->whereLessThanOrEqual('x.addedSince', new DateTime())
                    ->returning('x.y', 'x.z');
            })
            ->where('hello.z', '=', 'hello')
            ->andWhereExists(static function (Builder\SubQueryBuilder $q) {
                $q->matchingRelationship('x', 'HAHA', 'y')
                    ->matchingNode('Y');
            })
            ->creatingNode('Hello', 'h')
            ->creatingRelationship('x', 'HI_HA', 'h')
            ->batchCreating([
                ['h.x' => '1', 'h.y' => '2', 'hiHa.a' => 'b'],
                ['h.x' => '2', 'h.y' => '3', 'hiHa.a' => 'c'],
            ])
            ->returningAll()
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (test:Test),(otherTest:OtherTest),(test)-[goesTo:GOES_TO]->(otherTest)
        CALL {
            WITH *
            MATCH (x:XO),(otherTest)-[hello:Hello]->(x)
            WHERE x.addedSince <= $param0
            RETURN x.y, x.z
        }
        WHERE hello.z = $param1 AND EXISTS {
            MATCH (y:Y),(x)-[haha:HAHA]->(y)
        }
        UNWIND $param2 AS toCreate
        CREATE (h:Hello {x: toCreate['h.x'], y: toCreate['h.y']}),(x)-[hiHa:HI_HA {a: toCreate['hiHa.a']}]->(h)
        RETURN *
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testCreateFromBasic(): void
    {
        $cypher = QueryBuilder::from('Bar')
            ->creating(['foo' => 'woo'])
            ->toCypher();

        $expected = <<<'CYPHER'
        CREATE (bar:Bar {foo: $param0})
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testDynamicNameAndLabelNode(): void
    {
        $cypher = QueryBuilder::from('b:Bar')
            ->returning('foo', 'boo')
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (b:Bar)
        RETURN b.foo AS foo, b.boo AS boo
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }
}
