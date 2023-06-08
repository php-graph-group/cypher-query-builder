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

use PhpGraphGroup\CypherQueryBuilder\Common\Distinct;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;

interface ReturningBuilder
{
    /**
     * Returns the variables.
     */
    public function returning(string ...$properties): static;

    public function returningProcedure(string $function, string|null $alias = 'aggregate', Distinct|RawExpression|string ...$properties): self;

    /**
     * Returns all variables by using a wildcard ('*').
     */
    public function returningAll(): static;

    /**
     * Returns a raw expression.
     */
    public function returningRaw(string ...$cypher): static;

    /**
     * Returns only distinct rows.
     */
    public function distinct(bool $distinct = true): static;

    /**
     * Skips the result by the given number.
     */
    public function skipping(int $skip): static;

    /**
     * Limits the result by the given number.
     */
    public function limiting(int $limit): static;

    /**
     * Orders the result by the given properties and direction.
     *
     * @param 'ASC'|'DESC' $direction
     */
    public function orderingBy(string $direction = 'ASC', string|RawExpression ...$properties): static;

    /**
     * Returns the variables in its entirety.
     */
    public function returningVariables(string ...$variables): static;
}
