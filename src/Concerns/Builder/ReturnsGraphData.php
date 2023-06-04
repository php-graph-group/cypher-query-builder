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

use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Common\Variable;
use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Concerns\StringDecoder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\ReturningBuilder;

/**
 * @implements ReturningBuilder
 */
trait ReturnsGraphData
{
    use StringDecoder;
    use HasQueryStructure;

    public function returning(string ...$properties): static
    {
        foreach ($properties as $property) {
            $this->structure->return[] = $this->stringToAliasableProperty($property);
        }

        return $this;
    }

    public function returningAll(): static
    {
        $this->structure->return = [new RawExpression('*')];

        return $this;
    }

    public function returningRaw(string ...$cypher): static
    {
        foreach ($cypher as $raw) {
            $this->structure->return[] = new RawExpression($raw);
        }

        return $this;
    }

    public function distinct(bool $distinct = true): static
    {
        $this->structure->distinct = $distinct;

        return $this;
    }

    public function skipping(int $skip): static
    {
        $this->structure->skip = $skip;

        return $this;
    }

    public function limiting(int $limit): static
    {
        $this->structure->limit = $limit;

        return $this;
    }

    /**
     * @param 'ASC'|'DESC' $direction
     *
     * @return $this
     */
    public function orderingBy(string $direction = 'ASC', string|RawExpression ...$properties): static
    {
        $this->structure->orderByDirection = $direction;
        foreach ($properties as $property) {
            if (is_string($property)) {
                $property = $this->stringToProperty($property);
            }

            $this->structure->orderBys[] = $property;
        }

        return $this;
    }

    public function returningVariables(string ...$variables): static
    {
        foreach ($variables as $variable) {
            $this->structure->return[] = new Variable($variable);
        }

        return $this;
    }
}
