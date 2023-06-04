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

use Laudis\Neo4j\Databags\ResultSummary;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\ArrayList;
use Laudis\Neo4j\Types\Cartesian3DPoint;
use Laudis\Neo4j\Types\CartesianPoint;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Date;
use Laudis\Neo4j\Types\DateTime;
use Laudis\Neo4j\Types\Duration;
use Laudis\Neo4j\Types\LocalDateTime;
use Laudis\Neo4j\Types\LocalTime;
use Laudis\Neo4j\Types\Time;
use Laudis\Neo4j\Types\WGS843DPoint;
use Laudis\Neo4j\Types\WGS84Point;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\GrammarPipeline;
use WikibaseSolutions\CypherDSL\Query;

interface RunnableQueryBuilder
{
    /**
     * Counts the number of rows if no property is given. Counts the number of non-null properties otherwise.
     *
     * @see https://neo4j.com/docs/cypher-manual/current/functions/aggregating/
     */
    public function count(string|RawExpression|null $property = null, bool $distinct = false): int;

    /**
     * @see https://neo4j.com/docs/cypher-manual/current/functions/aggregating/#functions-avg
     */
    public function average(string|RawExpression $property): int|Duration|float|null;

    public function collect(string|RawExpression $property): CypherList;

    public function max(string|RawExpression $property): bool|Date|DateTime|Duration|float|int|ArrayList|LocalTime|LocalDateTime|WGS84Point|WGS843DPoint|CartesianPoint|Cartesian3DPoint|string|Time|CypherList;

    public function min(string|RawExpression $property): bool|Date|DateTime|Duration|float|int|ArrayList|LocalTime|LocalDateTime|WGS84Point|WGS843DPoint|CartesianPoint|Cartesian3DPoint|string|Time|CypherList;

    public function percentileCont(string|RawExpression $property, float $percentile): float;

    public function percentileDisc(string|RawExpression $property, float $percentile): float;

    public function stdDev(string|RawExpression $property): float;

    public function stdDevP(string|RawExpression $property): float;

    public function sum(string|RawExpression $property): float|int|Duration;

    public function update(array $values): ResultSummary;

    public function set(array $values): ResultSummary;

    public function batchCreate(array $rows): ResultSummary;

    public function create(array $values = []): ResultSummary;

    public function batchInsert(array $rows): ResultSummary;

    public function insert(array $values = []): ResultSummary;

    /**
     * Deletes the variables in the query from the database, even if they are still attached to other nodes.
     *
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/delete/#delete-a-node-with-all-its-relationships
     *
     * @param list<string>|string|null $variables
     */
    public function forceDelete(string ...$variables): ResultSummary;

    /**
     * Deletes the variables in the query from the database, even if they are still attached to other nodes.
     *
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/delete/#delete-a-node-with-all-its-relationships
     *
     * @param list<string>|string|null $variables
     */
    public function delete(array|string|null $variables): ResultSummary;

    public function merge(array $values = []): ResultSummary;

    public function execute(GrammarPipeline|null $pipeline = null): SummarizedResult;

    public function toCypher(GrammarPipeline|null $pipeline = null): string;

    public function toDsl(GrammarPipeline|null $pipeline = null): Query;

    public function first(): CypherMap;

    public function only(): mixed;

    public function returnAll(): SummarizedResult;

    /**
     * Only runs the read sections of the query.
     */
    public function get(string ...$properties): SummarizedResult;

    public function return(string ...$properties): SummarizedResult;

    public function pluck(string $property): ArrayList;

    /**
     * Deletes the variables in the query from the database.
     */
    public function remove(string ...$propertiesAndLabels): ResultSummary;

    public function returnRaw(string $cypher): mixed;

    public function aggregate(string $function, string|RawExpression $property): mixed;
}
