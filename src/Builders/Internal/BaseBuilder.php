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

use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PatternBuilder;
use RuntimeException;

/**
 * @template ParentType of PatternBuilder|null
 * @template ChildType of PatternBuilder
 */
abstract class BaseBuilder implements PatternBuilder
{
    /** @var list<ChildType> */
    protected array $children = [];

    /**
     * @param ParentType $parent
     */
    protected function __construct(
        protected readonly GraphPattern $pattern,
        protected readonly PatternBuilder|null $parent,
        protected readonly bool $optional
    ) {}

    /**
     * @param list<string>|string|null $label
     */
    public function addChildNode(array|string|null $label = null, string|null $name = null, bool $optional = false): NodePatternBuilder
    {
        $optional = $this->optional || $optional;
        $node = $this->pattern->addMatchingNode($label, $name, $optional);

        $builder = new NodePatternBuilder($this->pattern, $this, $node->name->name, false, $optional);

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->children[] = $builder;

        return $builder;
    }

    public function end(): PatternBuilder
    {
        if ($this->parent === null) {
            throw new RuntimeException('Cannot end a root builder');
        }

        return $this->parent;
    }

    public function getPattern(): GraphPattern
    {
        return $this->pattern;
    }
}
