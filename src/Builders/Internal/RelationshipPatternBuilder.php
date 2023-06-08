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
 * @extends BaseBuilder<NodePatternBuilder, NodePatternBuilder>
 */
class RelationshipPatternBuilder extends BaseBuilder
{
    /**
     * @param list<string>|string|null $type
     */
    public function __construct(
        GraphPattern $pattern,
        NodePatternBuilder $parent,
        private string|null $baseName,
        private readonly Direction|null $direction,
        private readonly array|string|null $type,
        bool $optional
    ) {
        parent::__construct($pattern, $parent, $optional);
    }

    public function addRelationship(array|string|null $type, string $name = null, Direction|null $direction = null, bool $optional = false): RelationshipPatternBuilder
    {
        $optional = $this->optional || $optional;

        // we create an anonymous intermediate node to connect a relationship with another
        // The anonymous node is skipped when running end(), so the API stays clean and the
        // end user does not have to worry about it.
        $intermediate = $this->addChildNode($type, $name, $optional);
        $intermediate->skip = true;

        $this->children[] = $intermediate;

        $child = new RelationshipPatternBuilder($this->pattern, $intermediate, $name, $direction, $type, $optional);
        $intermediate->children[] = $child;

        return $child;
    }

    public function end(): PatternBuilder
    {
        if (count($this->children) === 0) {
            $this->storeRelationship(null, 0);
        }

        foreach ($this->children as $i => $child) {
            $this->storeRelationship($child->name, $i);
        }

        if ($this->parent->skip) {
            return $this->parent->end();
        }

        return $this->parent;
    }

    public function storeRelationship(string|null $childName, int $count): void
    {
        /** @psalm-suppress PossiblyNullPropertyFetch */
        $rel = $this->pattern->addMatchingRelationship(
            $this->parent->name,
            $childName,
            $this->type,
            $this->baseName === null ? null : $this->baseName.($count > 0 ? $count : ''),
            $this->direction,
            $this->optional
        );

        if ($this->baseName === null) {
            $this->baseName = $rel->name->name;
        }
    }
}
