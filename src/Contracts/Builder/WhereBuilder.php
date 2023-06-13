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

namespace PhpGraphGroup\CypherQueryBuilder\Contracts\Builder;

use PhpGraphGroup\CypherQueryBuilder\Where\BooleanOperator;

/**
 * @psalm-type Operator = '='|'==='|'=='|'!='|'<>'|'!=='|'<'|'<='|'>'|'>='|'STARTS WITH'|'ENDS WITH'|'CONTAINS'|'IN'|'=~'|'LIKE'
 */
interface WhereBuilder
{
    /**
     * Create a boolean expression between a property and a value with the given operator.
     *
     * @param Operator $operator
     */
    public function where(string $property, string $operator, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static;

    /**
     * Create a boolean expression between a property and a value with the given operator.
     *
     * @param Operator $operator
     */
    public function orWhere(string $property, string $operator, mixed $value): static;

    /**
     * Create a boolean expression between a property and a value with the given operator.
     *
     * @param Operator $operator
     */
    public function xorWhere(string $property, string $operator, mixed $value): static;

    /**
     * Create a boolean expression between a property and a value with the given operator.
     *
     * @param Operator $operator
     */
    public function andWhere(string $property, string $operator, mixed $value): static;

    /**
     * Create a boolean expression between two properties with the given operator.
     *
     * @param Operator $operator
     */
    public function whereProperties(string $left, string $operator, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    /**
     * Create a boolean expression between two properties with the given operator.
     *
     * @param Operator $operator
     */
    public function orWhereProperties(string $left, string $operator, string $right): static;

    /**
     * Create a boolean expression between two properties with the given operator.
     *
     * @param Operator $operator
     */
    public function xorWhereProperties(string $left, string $operator, string $right): static;

    /**
     * Create a boolean expression between two properties with the given operator.
     *
     * @param Operator $operator
     */
    public function andWhereProperties(string $left, string $operator, string $right): static;

    /**
     * Create a boolean expression between a property and a value with the '=' operator.
     */
    public function whereEquals(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereEquals(string $property, mixed $value): static;

    public function xorWhereEquals(string $property, mixed $value): static;

    public function andWhereEquals(string $property, mixed $value): static;

    /**
     * Create a boolean expression between two properties with the '=' operator.
     */
    public function wherePropertiesEquals(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesEquals(string $left, string $right): static;

    public function xorWherePropertiesEquals(string $left, string $right): static;

    public function andWherePropertiesEquals(string $left, string $right): static;

    /**
     * Creates an EXISTS expression for the given sub query.
     *
     * @param callable(SubQueryBuilder) $builder
     */
    public function whereExists(callable $builder, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereExists(callable $builder): static;

    public function xorWhereExists(callable $builder): static;

    public function andWhereExists(callable $builder): static;

    /**
     * @param callable(WhereBuilder):void $builder
     *
     * @return $this
     */
    public function whereNot(callable $builder, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereNot(callable $builder): static;

    public function xorWhereNot(callable $builder): static;

    public function andWhereNot(callable $builder): static;

    /**
     * Creates a COUNT expression for the given sub query.
     *
     * @param callable(SubQueryBuilder):void $builder
     * @param '='|'!='|'<'|'<='|'>'|'>='     $operator
     *
     * @return $this
     */
    public function whereCount(callable $builder, int $count, string $operator = '=', BooleanOperator $chain = BooleanOperator::AND): static;

    /**
     * Creates a COUNT expression for the given sub query.
     *
     * @param callable(SubQueryBuilder):void $builder
     * @param '='|'!='|'<'|'<='|'>'|'>='     $operator
     *
     * @return $this
     */
    public function orWhereCount(callable $builder, int $count, string $operator = '='): static;

    /**
     * Creates a COUNT expression for the given sub query.
     *
     * @param callable(SubQueryBuilder):void $builder
     * @param '='|'!='|'<'|'<='|'>'|'>='     $operator
     *
     * @return $this
     */
    public function xorWhereCount(callable $builder, int $count, string $operator = '='): static;

    /**
     * Creates a COUNT expression for the given sub query.
     *
     * @param callable(SubQueryBuilder):void $builder
     * @param '='|'!='|'<'|'<='|'>'|'>='     $operator
     *
     * @return $this
     */
    public function andWhereCount(callable $builder, int $count, string $operator = '='): static;

    /**
     * @param callable(SubQueryBuilder):void $builder
     */
    public function whereNotExists(callable $builder, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereNotExists(callable $builder): static;

    public function xorWhereNotExists(callable $builder): static;

    public function andWhereNotExists(callable $builder): static;

    public function whereNull(string $property, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereNull(string $property): static;

    public function xorWhereNull(string $property): static;

    public function andWhereNull(string $property): static;

    public function whereNotNull(string $property, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereNotNull(string $property): static;

    public function xorWhereNotNull(string $property): static;

    public function andWhereNotNull(string $property): static;

    public function whereIn(string $property, array $values, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereIn(string $property, array $values): static;

    public function xorWhereIn(string $property, array $values): static;

    public function andWhereIn(string $property, array $values): static;

    public function wherePropertiesIn(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesIn(string $left, string $right): static;

    public function xorWherePropertiesIn(string $left, string $right): static;

    public function andWherePropertiesIn(string $left, string $right): static;

    public function whereContains(string $property, string $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereContains(string $property, string $value): static;

    public function xorWhereContains(string $property, string $value): static;

    public function andWhereContains(string $property, string $value): static;

    public function wherePropertiesContains(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesContains(string $left, string $right): static;

    public function xorWherePropertiesContains(string $left, string $right): static;

    public function andWherePropertiesContains(string $left, string $right): static;

    public function whereStartsWith(string $property, string $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereStartsWith(string $property, string $value): static;

    public function xorWhereStartsWith(string $property, string $value): static;

    public function andWhereStartsWith(string $property, string $value): static;

    public function wherePropertiesStartsWith(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesStartsWith(string $left, string $right): static;

    public function xorWherePropertiesStartsWith(string $left, string $right): static;

    public function andWherePropertiesStartsWith(string $left, string $right): static;

    public function whereEndsWith(string $property, string $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereEndsWith(string $property, string $value): static;

    public function xorWhereEndsWith(string $property, string $value): static;

    public function andWhereEndsWith(string $property, string $value): static;

    public function wherePropertiesEndsWith(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesEndsWith(string $left, string $right): static;

    public function xorWherePropertiesEndsWith(string $left, string $right): static;

    public function andWherePropertiesEndsWith(string $left, string $right): static;

    public function whereRegex(string $property, string $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereRegex(string $property, string $value): static;

    public function xorWhereRegex(string $property, string $value): static;

    public function andWhereRegex(string $property, string $value): static;

    public function wherePropertiesRegex(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesRegex(string $left, string $right): static;

    public function xorWherePropertiesRegex(string $left, string $right): static;

    public function andWherePropertiesRegex(string $left, string $right): static;

    public function whereLessThan(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereLessThan(string $property, mixed $value): static;

    public function xorWhereLessThan(string $property, mixed $value): static;

    public function andWhereLessThan(string $property, mixed $value): static;

    public function wherePropertiesLessThan(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesLessThan(string $left, string $right): static;

    public function xorWherePropertiesLessThan(string $left, string $right): static;

    public function andWherePropertiesLessThan(string $left, string $right): static;

    public function whereLessThanOrEqual(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereLessThanOrEqual(string $property, mixed $value): static;

    public function xorWhereLessThanOrEqual(string $property, mixed $value): static;

    public function andWhereLessThanOrEqual(string $property, mixed $value): static;

    public function wherePropertiesLessThanOrEqual(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesLessThanOrEqual(string $left, string $right): static;

    public function xorWherePropertiesLessThanOrEqual(string $left, string $right): static;

    public function andWherePropertiesLessThanOrEqual(string $left, string $right): static;

    public function whereGreaterThan(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereGreaterThan(string $property, mixed $value): static;

    public function xorWhereGreaterThan(string $property, mixed $value): static;

    public function andWhereGreaterThan(string $property, mixed $value): static;

    public function wherePropertiesGreaterThan(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesGreaterThan(string $left, string $right): static;

    public function xorWherePropertiesGreaterThan(string $left, string $right): static;

    public function andWherePropertiesGreaterThan(string $left, string $right): static;

    public function whereGreaterThanOrEqual(string $property, mixed $value, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereGreaterThanOrEqual(string $property, mixed $value): static;

    public function xorWhereGreaterThanOrEqual(string $property, mixed $value): static;

    public function andWhereGreaterThanOrEqual(string $property, mixed $value): static;

    public function wherePropertiesGreaterThanOrEqual(string $left, string $right, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWherePropertiesGreaterThanOrEqual(string $left, string $right): static;

    public function xorWherePropertiesGreaterThanOrEqual(string $left, string $right): static;

    public function andWherePropertiesGreaterThanOrEqual(string $left, string $right): static;

    /**
     * @param callable(WhereBuilder):void $builder
     */
    public function whereInner(callable $builder, BooleanOperator $chain = BooleanOperator::AND): static;

    public function orWhereInner(callable $builder): static;

    public function xorWhereInner(callable $builder): static;

    public function andWhereInner(callable $builder): static;

    public function whereRaw(string $cypher, BooleanOperator $chainingOperator): static;

    public function andWhereRaw(string $cypher): static;

    public function orWhereRaw(string $cypher): static;

    public function xorWhereRaw(string $cypher): static;
}
