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

use PhpGraphGroup\CypherQueryBuilder\Common\Parameter;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\SubQueryBuilder;
use PhpGraphGroup\CypherQueryBuilder\Where\BooleanOperator;

class SubQueryCountBooleanExpression
{
    /**
     * @param '<'|'<='|'='|'>'|'>='|'!=' $operator
     */
    public function __construct(
        public readonly SubQueryBuilder $left,
        public readonly string $operator,
        public readonly Parameter $right,
        public readonly BooleanOperator $chainingOperator
    ) {}
}
