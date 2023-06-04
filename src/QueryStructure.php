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

namespace PhpGraphGroup\CypherQueryBuilder;

use PhpGraphGroup\CypherQueryBuilder\Common\Alias;
use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;
use PhpGraphGroup\CypherQueryBuilder\Common\Parameter;
use PhpGraphGroup\CypherQueryBuilder\Common\ParameterStack;
use PhpGraphGroup\CypherQueryBuilder\Common\Property;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Common\Variable;
use PhpGraphGroup\CypherQueryBuilder\Set\LabelAssignment;
use PhpGraphGroup\CypherQueryBuilder\Set\PropertyAssignment;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\BinaryBooleanOperator;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\BinaryBooleanPropertyOperator;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\InnerWhereExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\NullBooleanExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\RawBoolean;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\SubQueryCountBooleanExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\SubQueryExistsExpression;

/**
 * This class defines the query structure that most closely resembles a SQL Builder.
 *
 * It provides an intermediary step between the query builder and the actual Cypher Grammar.
 *
 * The query structure is as follows:
 *
 *  MATCH <structure of nodes and relationships that are strictly joined together>
 *  |n OPTIONAL MATCH <structure of nodes and relationships that are left or right joined together>
 *  WHERE <conditions that are applied to the matched nodes and relationships>
 *  |n CALL {
 *  |*   < a sub query >
 *  |* }
 *  |* WITH *
 *  |n WHERE <conditions that are dependent on the sub queries result>
 *  CREATE <structure of nodes and relationships to create>
 *  SET <set expression on any of the nodes or relationships>
 *  |n MERGE <structure of nodes and relationships to merge>
 *  |* ON MATCH SET <set expression on any of the nodes or relationships>
 *  |n ON CREATE SET <set expression on any of the nodes or relationships>
 *  DELETE <variables to delete>
 *  RETURN <list of expressions to be returned. Group By expressions manipulate this section as well>
 *  SKIP <number of records to skip>
 *  LIMIT <number of records to return>
 *  UNION
 *  <A query to unionize>
 *
 * @internal
 */
final class QueryStructure
{
    /** @var list<BinaryBooleanOperator|BinaryBooleanPropertyOperator|InnerWhereExpression|NullBooleanExpression|RawBoolean|SubQueryExistsExpression|SubQueryCountBooleanExpression> */
    public array $wheres = [];

    /** @var list<QueryStructure> */
    public array $subQueries = [];

    /** @var list<Property|Variable|Alias|RawExpression> */
    public array $return = [];
    public Parameter|null $batchCreate = null;

    /** @var list<LabelAssignment|PropertyAssignment|RawExpression> */
    public array $set = [];

    /** @var list<LabelAssignment|PropertyAssignment|RawExpression> */
    public array $onMatch = [];

    /** @var list<LabelAssignment|PropertyAssignment|RawExpression> */
    public array $onCreate = [];

    /** @var list<Variable> */
    public array $delete = [];

    public bool $forceDelete = false;

    /** @var list<Property|LabelAssignment|RawExpression> */
    public array $remove = [];

    /** @var list<Property|RawExpression> */
    public array $orderBys = [];

    /** @var 'ASC'|'DESC' */
    public string $orderByDirection = 'ASC';

    public int|null $skip = null;

    public int|null $limit = null;

    public bool $distinct = false;

    public QueryStructure|null $unions = null;

    public function __construct(
        public readonly ParameterStack $parameters,
        public readonly GraphPattern $graphPattern,
        public readonly Variable $entry
    ) {}
}
