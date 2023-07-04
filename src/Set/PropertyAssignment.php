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

namespace PhpGraphGroup\CypherQueryBuilder\Set;

use PhpGraphGroup\CypherQueryBuilder\Common\MapValue;
use PhpGraphGroup\CypherQueryBuilder\Common\Parameter;
use PhpGraphGroup\CypherQueryBuilder\Common\Property;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;

/**
 * @template TValue = mixed
 */
class PropertyAssignment
{
    /**
     * @param TValue $value
     */
    public function __construct(
        public readonly Property $property,
        public readonly Parameter|MapValue|RawExpression $value
    ) {}
}
