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
use PhpGraphGroup\CypherQueryBuilder\Common\Property;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\WhereBuilder;
use PhpGraphGroup\CypherQueryBuilder\Where\BooleanOperator;

/**
 * @psalm-import-type Operator from WhereBuilder
 */
class BinaryBooleanOperator
{
    /**
     * @param Operator $operator
     */
    public function __construct(
        public readonly Property $left,
        public readonly string $operator,
        public readonly Parameter|RawExpression $right,
        public readonly BooleanOperator $chainingOperator
    ) {}
}
