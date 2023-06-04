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

namespace PhpGraphGroup\CypherQueryBuilder\Common;

class FunctionCall
{
    /**
     * @param list<Parameter|Property|RawExpression|Distinct> $arguments
     */
    public function __construct(
        public readonly string $function,
        public readonly array $arguments = []
    ) {}
}
