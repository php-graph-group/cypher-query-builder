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

namespace PhpGraphGroup\CypherQueryBuilder\Contracts;

use PhpGraphGroup\CypherQueryBuilder\Common\Direction;
use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;

interface PatternBuilder
{
    /**
     * @param list<string>|string|null $label
     */
    public function addChildNode(array|string $label = null, string $name = null, bool $optional = false): PatternBuilder;

    /**
     * @param list<string>|string|null $type
     */
    public function addRelationship(array|string|null $type, string $name = null, Direction $direction = null, bool $optional = false): PatternBuilder;

    public function end(): PatternBuilder;

    public function getPattern(): GraphPattern;
}
