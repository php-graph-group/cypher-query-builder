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

namespace PhpGraphGroup\CypherQueryBuilder\Where\Expressions;

use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\SubQueryBuilder;
use PhpGraphGroup\CypherQueryBuilder\Where\BooleanOperator;

class SubQueryExistsExpression
{
    public function __construct(
        public readonly SubQueryBuilder $subQuery,
        public readonly bool $negate,
        public readonly BooleanOperator $chainingOperator
    ) {}
}
