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

namespace PhpGraphGroup\CypherQueryBuilder\Builders\Internal;

use PhpGraphGroup\CypherQueryBuilder\Common\Direction;
use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PatternBuilder;

/**
 * @extends BaseBuilder<PatternBuilder|null, PatternBuilder>
 */
class NodePatternBuilder extends BaseBuilder
{
    public function __construct(
        GraphPattern $pattern,
        PatternBuilder|null $parent,
        public readonly string $name,
        public bool $skip,
        bool $optional
    ) {
        parent::__construct($pattern, $parent, $optional);
    }

    public function addRelationship(array|string|null $type, string $name = null, Direction|null $direction = null, bool $optional = false): PatternBuilder
    {
        $child = new RelationshipPatternBuilder($this->pattern, $this, $name, $direction, $type, $optional);

        $this->children[] = $child;

        return $child;
    }
}
