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
use PhpGraphGroup\CypherQueryBuilder\Builders\GraphPatternBuilder;
use PhpGraphGroup\CypherQueryBuilder\Common\Direction;
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

    public function testReturningProcedure(): void
    {
        $cypher = QueryBuilder::from('b:Bar')
            ->returningProcedure('callingme', 'aggregate', 'bar', 'boo')
            ->returningProcedure('callingOther', 'hello', 'zoo')
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (b:Bar)
        RETURN callingme(b.bar,b.boo) AS aggregate, callingOther(b.zoo) AS hello
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testDynamicRelationshipNode(): void
    {
        $cypher = QueryBuilder::fromRelationship('b:FOO')
            ->returning('bar AS zoo', 'boo')
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (anon0)-[b:FOO]->(anon1)
        RETURN b.bar AS zoo, b.boo AS boo
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testMerging(): void
    {
        $cypher = QueryBuilder::from('b:Foo')
            ->matchingNode('c:Bar')
            ->mergingConnection('b', 'FOO', 'c')
            ->mergingConnection('c', 'BAR', 'd:D')
            ->merging(['foo.bar' => 'awoo', 'x' => 'y'])
            ->whereProperties('c.createdAt', '=', 'c.updatedAt')
            ->onCreating(['foo.createdAt' => new DateTime(), 'createdAt' => new DateTime()])
            ->onMatching(['updatedAt' => new DateTime()])
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (c:Bar)
        WHERE c.createdAt = c.updatedAt
        MERGE (b:Foo {x: $param1}),(d:D),(b)-[foo:FOO {bar: $param0}]->(c),(c)-[bar:BAR]->(d)
        ON MATCH SET b.updatedAt = $param4
        ON CREATE SET foo.createdAt = $param2,b.createdAt = $param3
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testOptional(): void
    {
        $cypher = QueryBuilder::from('Foo')
            ->matchingRelationship('foo', 'ZOO', ':Bar', optional: true)
            ->returningAll()
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (foo:Foo)
        OPTIONAL MATCH (bar:Bar)
        OPTIONAL MATCH (foo)-[zoo:ZOO]->(bar)
        RETURN *
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testFromRelationship(): void
    {
        $cypher = QueryBuilder::fromRelationship('RELATIONSHIP')
            ->returningAll()
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (anon0)-[relationship:RELATIONSHIP]->(anon1)
        RETURN *
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testFromRelationshipEncoded(): void
    {
        $cypher = QueryBuilder::from('RELATIONSHIP>')
            ->returningAll()
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (anon0)-[relationship:RELATIONSHIP]->(anon1)
        RETURN *
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testFromRelationshipEncodedReverse(): void
    {
        $cypher = QueryBuilder::from('<RELATIONSHIP')
            ->returningAll()
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (anon0)<-[relationship:RELATIONSHIP]-(anon1)
        RETURN *
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function testFromPatternBuilder(): void
    {
        $pattern = GraphPatternBuilder::from('Foo')
            ->addChildNode('Bar')->end()
            ->addRelationship('FOO_BAZ')
                ->addChildNode('Baz')->end()
                ->addChildNode('Baz', 'baz2')->end()
                ->addChildNode('Reverse')
                    ->addRelationship('REVERSING', direction: Direction::RIGHT_TO_LEFT)
                        ->addChildNode('Reversed')->end()
                    ->end()
                ->end()
                ->addChildNode()
                    ->addRelationship('ANONYMOUS')
                        ->addChildNode()->end()
                    ->end()
                ->end()
            ->end()
            ->addRelationship('OPTIONAL', optional: true)
                ->addChildNode('OptionalNode')->end()
            ->end();

        $cypher = QueryBuilder::fromPatternBuilder($pattern)->returningAll()->toCypher();

        $expected = <<<'CYPHER'
        MATCH (foo:Foo),(bar:Bar),(baz:Baz),(baz2:Baz),(reverse:Reverse),(reversed:Reversed),(anon0),(anon1),(reverse)<-[reversing:REVERSING]-(reversed),(anon0)-[anonymous:ANONYMOUS]->(anon1),(foo)-[fooBaz:FOO_BAZ]->(baz),(foo)-[fooBaz1:FOO_BAZ]->(baz2),(foo)-[fooBaz2:FOO_BAZ]->(reverse),(foo)-[fooBaz3:FOO_BAZ]->(anon0)
        OPTIONAL MATCH (optionalNode:OptionalNode)
        OPTIONAL MATCH (foo)-[optional:OPTIONAL]->(optionalNode)
        RETURN *
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }

    public function testComplexEncoding(): void
    {
        $cypher = QueryBuilder::from('otherNode:`backtick')
            ->returning('x', 'y')
            ->toCypher();

        $expected = <<<'CYPHER'
        MATCH (otherNode:```backtick`)
        RETURN otherNode.x AS x, otherNode.y AS y
        CYPHER;

        $this->assertEqualCypher($expected, $cypher);
    }
}
