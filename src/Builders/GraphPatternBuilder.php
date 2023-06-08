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

namespace PhpGraphGroup\CypherQueryBuilder\Builders;

use PhpGraphGroup\CypherQueryBuilder\Builders\Internal\NodePatternBuilder;
use PhpGraphGroup\CypherQueryBuilder\Common\Direction;
use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PatternBuilder;

class GraphPatternBuilder
{
    /**
     * @param list<string>|string|null $labelOrType
     */
    public static function from(array|string|null $labelOrType = null, string|null $name = null, bool $optional = false): PatternBuilder
    {
        $firstLabelOrType = (is_array($labelOrType) ? ($labelOrType[0] ?? null) : $labelOrType) ?? '';
        if (str_starts_with($firstLabelOrType, '<') || str_ends_with($firstLabelOrType, '>')) {
            return self::fromRelationship($labelOrType, $name, optional: $optional);
        }

        return self::fromNode($labelOrType, $name, optional: $optional);
    }

    /**
     * @param list<string>|string|null $label
     */
    public static function fromNode(array|string|null $label = null, string|null $name = null, bool $optional = false): PatternBuilder
    {
        $pattern = new GraphPattern();

        $part = $pattern->addMatchingNode($label, $name, $optional);

        return new NodePatternBuilder($pattern, null, $part->name->name, false, $optional);
    }

    /**
     * @param list<string>|string|null $type
     */
    public static function fromRelationship(array|string|null $type = null, string|null $name = null, Direction|null $direction = null, bool $optional = false): PatternBuilder
    {
        /** @var NodePatternBuilder */
        $parent = self::fromNode(optional: $optional);
        $parent->skip = true;

        return $parent->addRelationship($type, $name, $direction, $optional);
    }
}
