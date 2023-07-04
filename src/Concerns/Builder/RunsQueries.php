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
use PhpGraphGroup\CypherQueryBuilder\Common\Alias;
use PhpGraphGroup\CypherQueryBuilder\Common\Distinct;
use PhpGraphGroup\CypherQueryBuilder\Common\FunctionCall;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Concerns\StringDecoder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\RunnableQueryBuilder;
use PhpGraphGroup\CypherQueryBuilder\GrammarPipeline;
use WikibaseSolutions\CypherDSL\Query;

/**
 * @implements RunnableQueryBuilder
 */
trait RunsQueries
{
    use HasQueryStructure;
    use StringDecoder;
    use HasStaticClient;
    use SetBuilder;
    use CreatesGraphElements;
    use DeletesGraphElements;
    use MergesGraphElements;

    public function count(string|RawExpression $property = null, bool $distinct = false): int
    {
        if ($property === null) {
            $property = new RawExpression('*');
        }

        if ($distinct) {
            if (is_string($property)) {
                $property = $this->stringToProperty($property);
            }
            $property = new Distinct($property);
        }

        $this->prepareAggregation('count', $property);

        return $this->runAsReadQuery()
            ->getAsCypherMap(0)
            ->getAsInt('count');
    }

    private function runAsReadQuery(): SummarizedResult
    {
        $query = $this->adapter->readOnlyQuery($this->structure)->toQuery();

        return $this->run($query, $this->structure->parameters);
    }

    /**
     * @see https://neo4j.com/docs/cypher-manual/current/functions/aggregating/#functions-avg
     */
    public function average(string|RawExpression $property): int|float|Duration|null
    {
        /** @var int|float|Duration|null */
        return $this->aggregate('avg', $property);
    }

    public function collect(string|RawExpression $property): CypherList
    {
        return $this->aggregate('collect', $property);
    }

    public function max(string|RawExpression $property): bool|Date|DateTime|Duration|float|int|ArrayList|LocalTime|LocalDateTime|WGS84Point|WGS843DPoint|CartesianPoint|Cartesian3DPoint|string|Time|CypherList
    {
        /** @var bool|Date|DateTime|Duration|float|int|ArrayList|LocalTime|LocalDateTime|WGS84Point|WGS843DPoint|CartesianPoint|Cartesian3DPoint|string|Time|CypherList */
        return $this->aggregate('max', $property);
    }

    public function min(string|RawExpression $property): bool|Date|DateTime|Duration|float|int|ArrayList|LocalTime|LocalDateTime|WGS84Point|WGS843DPoint|CartesianPoint|Cartesian3DPoint|string|Time|CypherList
    {
        /** @var bool|Date|DateTime|Duration|float|int|ArrayList|LocalTime|LocalDateTime|WGS84Point|WGS843DPoint|CartesianPoint|Cartesian3DPoint|string|Time|CypherList */
        return $this->aggregate('min', $property);
    }

    public function aggregate(string $function, string|RawExpression $property): mixed
    {
        $this->prepareAggregation($function, $property);

        return $this->runAsReadQuery()
            ->getAsCypherMap(0)
            ->get('aggregate');
    }

    public function percentileCont(string|RawExpression $property, float $percentile): float
    {
        return $this->asPercentile('percentileCont', $property, $percentile);
    }

    public function percentileDisc(string|RawExpression $property, float $percentile): float
    {
        return $this->asPercentile('percentileDisc', $property, $percentile);
    }

    public function stdDev(string|RawExpression $property): float
    {
        /** @var float */
        return $this->aggregate('stdDev', $property);
    }

    public function stdDevP(string|RawExpression $property): float
    {
        /** @var float */
        return $this->aggregate('stdDevP', $property);
    }

    public function sum(string|RawExpression $property): float|int|Duration
    {
        /** @var float */
        return $this->aggregate('sum', $property);
    }

    public function update(array $values): ResultSummary
    {
        return $this->set($values);
    }

    public function set(array $values): ResultSummary
    {
        $this->settingMany($values);

        $query = $this->adapter->setQuery($this->structure)->toQuery();

        return $this->run($query, $this->structure->parameters)->getSummary();
    }

    public function batchCreate(array $rows): ResultSummary
    {
        $this->batchCreating($rows);

        return $this->runCreatePipeline();
    }

    public function create(array $values = []): ResultSummary
    {
        $this->creating($values);

        return $this->runCreatePipeline();
    }

    public function batchInsert(array $rows): ResultSummary
    {
        return $this->batchCreate($rows);
    }

    public function insert(array $values = []): ResultSummary
    {
        return $this->batchInsert($values);
    }

    /**
     * Deletes the variables in the query from the database, even if they are still attached to other nodes.
     *
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/delete/#delete-a-node-with-all-its-relationships
     *
     * @param list<string>|string|null $variables
     */
    public function forceDelete(string ...$variables): ResultSummary
    {
        $this->forcingDeletion(...$variables);

        $query = $this->adapter->delete($this->structure)->toQuery();

        return $this->run($query, $this->structure->parameters)->getSummary();
    }

    /**
     * Deletes the variables in the query from the database, even if they are still attached to other nodes.
     *
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/delete/#delete-a-node-with-all-its-relationships
     *
     * @param list<string>|string|null $variables
     */
    public function delete(array|string|null $variables): ResultSummary
    {
        $this->deleting(...$variables);

        $query = $this->adapter->delete($this->structure)->toQuery();

        return $this->run($query, $this->structure->parameters)->getSummary();
    }

    public function merge(array $values = []): ResultSummary
    {
        $this->merging($values);

        $query = $this->adapter->merge($this->structure)
            ->toQuery();

        return $this->run($query, $this->structure->parameters)->getSummary();
    }

    public function execute(GrammarPipeline $pipeline = null): SummarizedResult
    {
        $query = $this->toCypher($pipeline);

        return $this->run($query, $this->structure->parameters);
    }

    public function toCypher(GrammarPipeline $pipeline = null): string
    {
        return $this->toDsl($pipeline)->toQuery();
    }

    public function toDsl(GrammarPipeline $pipeline = null): Query
    {
        return $this->adapter->fullQuery($this->structure);
    }

    public function first(): CypherMap
    {
        $structure = clone $this->structure;

        $structure->limit = 1;

        $query = $this->adapter->fullQuery($structure);

        return $this->run($query->toQuery(), $this->structure->parameters)->getAsCypherMap(0);
    }

    public function only(): mixed
    {
        return $this->first()
            ->first()
            ->getValue();
    }

    public function returnAll(): SummarizedResult
    {
        $this->returningAll();

        return $this->get();
    }

    /**
     * Only runs the read sections of the query.
     */
    public function get(string ...$properties): SummarizedResult
    {
        return $this->return(...$properties);
    }

    public function return(string ...$properties): SummarizedResult
    {
        $this->returning(...$properties);

        $query = $this->adapter->readOnlyQuery($this->structure)->toQuery();

        return $this->run($query, $this->structure->parameters);
    }

    public function pluck(string $property): ArrayList
    {
        $this->structure->return = [$this->stringToProperty($property)];

        return $this->get()->pluck($property);
    }

    /**
     * Deletes the variables in the query from the database.
     */
    public function remove(string ...$propertiesAndLabels): ResultSummary
    {
        $this->removing(...$propertiesAndLabels);

        $query = $this->adapter->removeQuery($this->structure);

        return $this->run($query->toQuery(), $this->structure->parameters)->getSummary();
    }

    public function returnRaw(string $cypher): SummarizedResult
    {
        $this->returningRaw($cypher);

        return $this->runAsReadQuery();
    }

    public function prepareAggregation(string $function, Distinct|RawExpression|string $property): void
    {
        if (is_string($property)) {
            $property = $this->stringToProperty($property);
        }

        $this->structure->return = [new Alias(new FunctionCall($function, [$property]), 'aggregate')];
    }

    public function asPercentile(string $function, RawExpression|string $property, float $percentile): float
    {
        if (is_string($property)) {
            $property = $this->stringToProperty($property);
        }

        $param = $this->structure->parameters->add($percentile);

        $this->structure->return = [new ALias(new FunctionCall($function, [$property, $param]), 'aggregate')];

        return $this->runAsReadQuery()
            ->getAsCypherMap(0)
            ->getAsFloat('aggregate');
    }

    public function runCreatePipeline(): ResultSummary
    {
        $query = $this->adapter->createQuery($this->structure)->toQuery();

        return $this->run($query, $this->structure->parameters)->getSummary();
    }
}
