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

use PhpGraphGroup\CypherQueryBuilder\Set\PropertyAssignment;

class PropertyRelationship
{
    /**
     * @param list<string>             $types
     * @param list<PropertyAssignment> $properties
     */
    public function __construct(
        public readonly Variable|null $name,
        public readonly Variable|null $start,
        public readonly Variable|null $end,
        public readonly array $types,
        public readonly array $properties
    ) {}
}
