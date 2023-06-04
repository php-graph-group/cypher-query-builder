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

use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Concerns\StringDecoder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\WhereBuilder;
use PhpGraphGroup\CypherQueryBuilder\Where\BooleanOperator;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\BinaryBooleanOperator;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\BinaryBooleanPropertyOperator;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\InnerWhereExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\NullBooleanExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\RawBoolean;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\SubQueryCountBooleanExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\SubQueryExistsExpression;

/**
 * @implements WhereBuilder
 */
trait FiltersGraphTraversal
{
    use HasQueryStructure;
    use StringDecoder;

    /**
     * @param '='|'!='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'IN'|'=~' $operator
     */
    public function where(string $property, string $operator, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $property = $this->stringToProperty($property);
        $value = $this->structure->parameters->add($value);

        $this->structure->wheres[] = new BinaryBooleanOperator($property, $operator, $value, $chain);

        return $this;
    }

    /**
     * @param '='|'!='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'IN'|'=~' $operator
     */
    public function orWhere(string $property, string $operator, mixed $value): static
    {
        return $this->where($property, $operator, $value, BooleanOperator::OR);
    }

    /**
     * @param '='|'!='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'IN'|'=~' $operator
     */
    public function xorWhere(string $property, string $operator, mixed $value): static
    {
        return $this->where($property, $operator, $value, BooleanOperator::XOR);
    }

    /**
     * @param '='|'!='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'IN'|'=~' $operator
     */
    public function andWhere(string $property, string $operator, mixed $value): static
    {
        return $this->where($property, $operator, $value, BooleanOperator::AND);
    }

    /**
     * Create a boolean expression between two properties with the given operator.
     *
     * @param '='|'!='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'=~'|'IN' $operator
     */
    public function whereProperties(string $left, string $operator, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $left = $this->stringToProperty($left);
        $right = $this->stringToProperty($right);

        $this->structure->wheres[] = new BinaryBooleanPropertyOperator($left, $operator, $right, $chain);

        return $this;
    }

    /**
     * Create a boolean expression between two properties with the given operator.
     *
     * @param '='|'!='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'=~'|'IN' $operator
     */
    public function orWhereProperties(string $left, string $operator, string $right): static
    {
        return $this->whereProperties($left, $operator, $right, BooleanOperator::OR);
    }

    /**
     * Create a boolean expression between two properties with the given operator.
     *
     * @param '='|'!='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'=~'|'IN' $operator
     */
    public function xorWhereProperties(string $left, string $operator, string $right): static
    {
        return $this->whereProperties($left, $operator, $right, BooleanOperator::XOR);
    }

    /**
     * Create a boolean expression between two properties with the given operator.
     *
     * @param '='|'!='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'=~'|'IN' $operator
     */
    public function andWhereProperties(string $left, string $operator, string $right): static
    {
        return $this->whereProperties($left, $operator, $right, BooleanOperator::AND);
    }

    public function whereIn(string $property, array $values, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $property = $this->stringToProperty($property);
        $value = $this->structure->parameters->add($values);

        $this->structure->wheres[] = new BinaryBooleanOperator($property, 'IN', $value, $chain);

        return $this;
    }

    public function orWhereIn(string $property, array $values): static
    {
        return $this->whereIn($property, $values, BooleanOperator::OR);
    }

    public function andWhereIn(string $property, array $values): static
    {
        return $this->whereIn($property, $values, BooleanOperator::AND);
    }

    public function xorWhereIn(string $property, array $values): static
    {
        return $this->whereIn($property, $values, BooleanOperator::XOR);
    }

    public function whereNull(string $property, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $property = $this->stringToProperty($property);

        $this->structure->wheres[] = new NullBooleanExpression($property, false, $chain);

        return $this;
    }

    public function orWhereNull(string $property): static
    {
        return $this->whereNull($property, BooleanOperator::OR);
    }

    public function andWhereNull(string $property): static
    {
        return $this->whereNull($property, BooleanOperator::AND);
    }

    public function xorWhereNull(string $property): static
    {
        return $this->whereNull($property, BooleanOperator::XOR);
    }

    public function whereNotNull(string $property, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $property = $this->stringToProperty($property);

        $this->structure->wheres[] = new NullBooleanExpression($property, true, $chain);

        return $this;
    }

    public function orWhereNotNull(string $property): static
    {
        return $this->whereNotNull($property, BooleanOperator::OR);
    }

    public function andWhereNotNull(string $property): static
    {
        return $this->whereNotNull($property, BooleanOperator::AND);
    }

    public function xorWhereNotNull(string $property): static
    {
        return $this->whereNotNull($property, BooleanOperator::XOR);
    }

    public function whereExists(callable $builder, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $query = $this->createSubQueryBuilder();

        $builder($query);

        $this->structure->wheres[] = new SubQueryExistsExpression($query, false, $chain);

        return $this;
    }

    public function orWhereExists(callable $builder): static
    {
        return $this->whereExists($builder, BooleanOperator::OR);
    }

    public function andWhereExists(callable $builder): static
    {
        return $this->whereExists($builder, BooleanOperator::AND);
    }

    public function xorWhereExists(callable $builder): static
    {
        return $this->whereExists($builder, BooleanOperator::XOR);
    }

    public function whereNotExists(callable $builder, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $query = $this->createSubQueryBuilder();

        $builder($query);

        $this->structure->wheres[] = new SubQueryExistsExpression($query, true, $chain);

        return $this;
    }

    public function orWhereNotExists(callable $builder): static
    {
        return $this->whereNotExists($builder, BooleanOperator::OR);
    }

    public function andWhereNotExists(callable $builder): static
    {
        return $this->whereNotExists($builder, BooleanOperator::AND);
    }

    public function xorWhereNotExists(callable $builder): static
    {
        return $this->whereNotExists($builder, BooleanOperator::XOR);
    }

    public function whereEquals(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, '=', $value, $chain);
    }

    public function orWhereEquals(string $property, mixed $value): static
    {
        return $this->whereEquals($property, $value, BooleanOperator::OR);
    }

    public function andWhereEquals(string $property, mixed $value): static
    {
        return $this->whereEquals($property, $value, BooleanOperator::AND);
    }

    public function xorWhereEquals(string $property, mixed $value): static
    {
        return $this->whereEquals($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesEquals(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, '=', $right, $chain);
    }

    public function orWherePropertiesEquals(string $left, string $right): static
    {
        return $this->wherePropertiesEquals($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesEquals(string $left, string $right): static
    {
        return $this->wherePropertiesEquals($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesEquals(string $left, string $right): static
    {
        return $this->wherePropertiesEquals($left, $right, BooleanOperator::XOR);
    }

    public function whereNot(callable $builder, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $query = $builder($this->createSubQueryBuilder());

        $this->structure->wheres[] = new InnerWhereExpression($query, true, $chain);

        return $this;
    }

    public function orWhereNot(callable $builder): static
    {
        return $this->whereNot($builder, BooleanOperator::OR);
    }

    public function andWhereNot(callable $builder): static
    {
        return $this->whereNot($builder, BooleanOperator::AND);
    }

    public function xorWhereNot(callable $builder): static
    {
        return $this->whereNot($builder, BooleanOperator::XOR);
    }

    /**
     * @param '='|'!='|'<'|'<='|'>'|'>=' $operator
     *
     * @return $this
     */
    public function whereCount(callable $builder, int $count, string $operator = '=', BooleanOperator $chain = BooleanOperator::AND): static
    {
        $query = $this->createSubQueryBuilder();
        $builder($query);
        $param = $this->structure->parameters->add($count);

        $this->structure->wheres[] = new SubQueryCountBooleanExpression($query, $operator, $param, $chain);

        return $this;
    }

    /**
     * @param '='|'!='|'<'|'<='|'>'|'>=' $operator
     *
     * @return $this
     */
    public function orWhereCount(callable $builder, int $count, string $operator = '='): static
    {
        return $this->whereCount($builder, $count, $operator, BooleanOperator::OR);
    }

    /**
     * @param '='|'!='|'<'|'<='|'>'|'>=' $operator
     *
     * @return $this
     */
    public function andWhereCount(callable $builder, int $count, string $operator = '='): static
    {
        return $this->whereCount($builder, $count, $operator, BooleanOperator::AND);
    }

    /**
     * @param '='|'!='|'<'|'<='|'>'|'>=' $operator
     *
     * @return $this
     */
    public function xorWhereCount(callable $builder, int $count, string $operator = '='): static
    {
        return $this->whereCount($builder, $count, $operator, BooleanOperator::XOR);
    }

    public function wherePropertiesIn(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, 'IN', $right, $chain);
    }

    public function orWherePropertiesIn(string $left, string $right): static
    {
        return $this->wherePropertiesIn($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesIn(string $left, string $right): static
    {
        return $this->wherePropertiesIn($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesIn(string $left, string $right): static
    {
        return $this->wherePropertiesIn($left, $right, BooleanOperator::XOR);
    }

    public function whereContains(string $property, string $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, 'CONTAINS', $value, $chain);
    }

    public function orWhereContains(string $property, string $value): static
    {
        return $this->whereContains($property, $value, BooleanOperator::OR);
    }

    public function andWhereContains(string $property, string $value): static
    {
        return $this->whereContains($property, $value, BooleanOperator::AND);
    }

    public function xorWhereContains(string $property, string $value): static
    {
        return $this->whereContains($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesContains(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, 'CONTAINS', $right, $chain);
    }

    public function orWherePropertiesContains(string $left, string $right): static
    {
        return $this->wherePropertiesContains($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesContains(string $left, string $right): static
    {
        return $this->wherePropertiesContains($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesContains(string $left, string $right): static
    {
        return $this->wherePropertiesContains($left, $right, BooleanOperator::XOR);
    }

    public function whereStartsWith(string $property, string $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, 'STARTS WITH', $value, $chain);
    }

    public function orWhereStartsWith(string $property, string $value): static
    {
        return $this->whereStartsWith($property, $value, BooleanOperator::OR);
    }

    public function andWhereStartsWith(string $property, string $value): static
    {
        return $this->whereStartsWith($property, $value, BooleanOperator::AND);
    }

    public function xorWhereStartsWith(string $property, string $value): static
    {
        return $this->whereStartsWith($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesStartsWith(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, 'STARTS WITH', $right, $chain);
    }

    public function orWherePropertiesStartsWith(string $left, string $right): static
    {
        return $this->wherePropertiesStartsWith($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesStartsWith(string $left, string $right): static
    {
        return $this->wherePropertiesStartsWith($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesStartsWith(string $left, string $right): static
    {
        return $this->wherePropertiesStartsWith($left, $right, BooleanOperator::XOR);
    }

    public function whereEndsWith(string $property, string $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, 'ENDS WITH', $value, $chain);
    }

    public function orWhereEndsWith(string $property, string $value): static
    {
        return $this->whereEndsWith($property, $value, BooleanOperator::OR);
    }

    public function andWhereEndsWith(string $property, string $value): static
    {
        return $this->whereEndsWith($property, $value, BooleanOperator::AND);
    }

    public function xorWhereEndsWith(string $property, string $value): static
    {
        return $this->whereEndsWith($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesEndsWith(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, 'ENDS WITH', $right, $chain);
    }

    public function orWherePropertiesEndsWith(string $left, string $right): static
    {
        return $this->wherePropertiesEndsWith($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesEndsWith(string $left, string $right): static
    {
        return $this->wherePropertiesEndsWith($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesEndsWith(string $left, string $right): static
    {
        return $this->wherePropertiesEndsWith($left, $right, BooleanOperator::XOR);
    }

    public function whereRegex(string $property, string $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, '=~', $value, $chain);
    }

    public function orWhereRegex(string $property, string $value): static
    {
        return $this->whereRegex($property, $value, BooleanOperator::OR);
    }

    public function andWhereRegex(string $property, string $value): static
    {
        return $this->whereRegex($property, $value, BooleanOperator::AND);
    }

    public function xorWhereRegex(string $property, string $value): static
    {
        return $this->whereRegex($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesRegex(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, '=~', $right, $chain);
    }

    public function orWherePropertiesRegex(string $left, string $right): static
    {
        return $this->wherePropertiesRegex($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesRegex(string $left, string $right): static
    {
        return $this->wherePropertiesRegex($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesRegex(string $left, string $right): static
    {
        return $this->wherePropertiesRegex($left, $right, BooleanOperator::XOR);
    }

    public function whereLessThan(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, '<', $value, $chain);
    }

    public function orWhereLessThan(string $property, mixed $value): static
    {
        return $this->whereLessThan($property, $value, BooleanOperator::OR);
    }

    public function andWhereLessThan(string $property, mixed $value): static
    {
        return $this->whereLessThan($property, $value, BooleanOperator::AND);
    }

    public function xorWhereLessThan(string $property, mixed $value): static
    {
        return $this->whereLessThan($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesLessThan(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, '<', $right, $chain);
    }

    public function orWherePropertiesLessThan(string $left, string $right): static
    {
        return $this->wherePropertiesLessThan($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesLessThan(string $left, string $right): static
    {
        return $this->wherePropertiesLessThan($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesLessThan(string $left, string $right): static
    {
        return $this->wherePropertiesLessThan($left, $right, BooleanOperator::XOR);
    }

    public function whereLessThanOrEqual(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, '<=', $value, $chain);
    }

    public function orWhereLessThanOrEqual(string $property, mixed $value): static
    {
        return $this->whereLessThanOrEqual($property, $value, BooleanOperator::OR);
    }

    public function andWhereLessThanOrEqual(string $property, mixed $value): static
    {
        return $this->whereLessThanOrEqual($property, $value, BooleanOperator::AND);
    }

    public function xorWhereLessThanOrEqual(string $property, mixed $value): static
    {
        return $this->whereLessThanOrEqual($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesLessThanOrEqual(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, '<=', $right, $chain);
    }

    public function orWherePropertiesLessThanOrEqual(string $left, string $right): static
    {
        return $this->wherePropertiesLessThanOrEqual($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesLessThanOrEqual(string $left, string $right): static
    {
        return $this->wherePropertiesLessThanOrEqual($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesLessThanOrEqual(string $left, string $right): static
    {
        return $this->wherePropertiesLessThanOrEqual($left, $right, BooleanOperator::XOR);
    }

    public function whereGreaterThan(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, '>', $value, $chain);
    }

    public function orWhereGreaterThan(string $property, mixed $value): static
    {
        return $this->whereGreaterThan($property, $value, BooleanOperator::OR);
    }

    public function andWhereGreaterThan(string $property, mixed $value): static
    {
        return $this->whereGreaterThan($property, $value, BooleanOperator::AND);
    }

    public function xorWhereGreaterThan(string $property, mixed $value): static
    {
        return $this->whereGreaterThan($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesGreaterThan(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, '>', $right, $chain);
    }

    public function orWherePropertiesGreaterThan(string $left, string $right): static
    {
        return $this->wherePropertiesGreaterThan($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesGreaterThan(string $left, string $right): static
    {
        return $this->wherePropertiesGreaterThan($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesGreaterThan(string $left, string $right): static
    {
        return $this->wherePropertiesGreaterThan($left, $right, BooleanOperator::XOR);
    }

    public function whereGreaterThanOrEqual(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->where($property, '>=', $value, $chain);
    }

    public function orWhereGreaterThanOrEqual(string $property, mixed $value): static
    {
        return $this->whereGreaterThanOrEqual($property, $value, BooleanOperator::OR);
    }

    public function andWhereGreaterThanOrEqual(string $property, mixed $value): static
    {
        return $this->whereGreaterThanOrEqual($property, $value, BooleanOperator::AND);
    }

    public function xorWhereGreaterThanOrEqual(string $property, mixed $value): static
    {
        return $this->whereGreaterThanOrEqual($property, $value, BooleanOperator::XOR);
    }

    public function wherePropertiesGreaterThanOrEqual(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static
    {
        return $this->whereProperties($left, '>=', $right, $chain);
    }

    public function orWherePropertiesGreaterThanOrEqual(string $left, string $right): static
    {
        return $this->wherePropertiesGreaterThanOrEqual($left, $right, BooleanOperator::OR);
    }

    public function andWherePropertiesGreaterThanOrEqual(string $left, string $right): static
    {
        return $this->wherePropertiesGreaterThanOrEqual($left, $right, BooleanOperator::AND);
    }

    public function xorWherePropertiesGreaterThanOrEqual(string $left, string $right): static
    {
        return $this->wherePropertiesGreaterThanOrEqual($left, $right, BooleanOperator::XOR);
    }

    public function whereInner(callable $builder, BooleanOperator $chain = BooleanOperator::AND): static
    {
        $wheres = $this->createSubQueryBuilder();

        $builder($wheres);

        $structure = $wheres->getStructure();
        if (count($structure->wheres) > 0) {
            $this->structure->wheres[] = new InnerWhereExpression($structure->wheres, false, $chain);
        }

        return $this;
    }

    public function orWhereInner(callable $builder): static
    {
        return $this->whereInner($builder, BooleanOperator::OR);
    }

    public function andWhereInner(callable $builder): static
    {
        return $this->whereInner($builder, BooleanOperator::AND);
    }

    public function xorWhereInner(callable $builder): static
    {
        return $this->whereInner($builder, BooleanOperator::XOR);
    }

    public function whereRaw(string $cypher, BooleanOperator $chainingOperator): static
    {
        $this->structure->wheres[] = new RawBoolean($cypher, $chainingOperator);

        return $this;
    }

    public function orWhereRaw(string $cypher): static
    {
        return $this->whereRaw($cypher, BooleanOperator::OR);
    }

    public function andWhereRaw(string $cypher): static
    {
        return $this->whereRaw($cypher, BooleanOperator::AND);
    }

    public function xorWhereRaw(string $cypher): static
    {
        return $this->whereRaw($cypher, BooleanOperator::XOR);
    }
}
