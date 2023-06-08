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

namespace PhpGraphGroup\CypherQueryBuilder\Concerns;

use InvalidArgumentException;
use PhpGraphGroup\CypherQueryBuilder\Adapter\BuilderToDSLAdapter;
use PhpGraphGroup\CypherQueryBuilder\Common\Direction;
use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;
use PhpGraphGroup\CypherQueryBuilder\Common\ParameterStack;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PatternBuilder;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;

trait Builder
{
    public static function fromPatternBuilder(PatternBuilder $builder): self
    {
        $pattern = $builder->getPattern();
        /** @psalm-suppress UndefinedPropertyFetch */
        $start = $pattern->chunk('match')[0]->name;

        if ($start === null) {
            throw new InvalidArgumentException('Name is required when using a GraphPatternBuilder');
        }

        return new self(new QueryStructure(
            new ParameterStack(),
            $pattern,
            $start,
        ), new BuilderToDSLAdapter());
    }

    /**
     * @param PatternBuilder|list<string>|string|null $labelOrType
     */
    public static function from(PatternBuilder|array|string|null $labelOrType = null, string|null $name = null, bool $optional = false): self
    {
        if ($labelOrType instanceof PatternBuilder) {
            return self::fromPatternBuilder($labelOrType);
        }

        $firstLabelOrType = (is_array($labelOrType) ? ($labelOrType[0] ?? null) : $labelOrType) ?? '';
        if (str_starts_with($firstLabelOrType, '<') || str_ends_with($firstLabelOrType, '>')) {
            return self::fromRelationship($labelOrType, $name, optional: $optional);
        }

        return self::fromNode($labelOrType, $name, optional: $optional);
    }

    /**
     * @param list<string>|string|null $type
     */
    public static function fromRelationship(array|string|null $type = null, string|null $name = null, Direction|null $direction = null, bool $optional = false): self
    {
        $matches = new GraphPattern();
        $relationship = $matches->addMatchingRelationship(null, null, $type, $name, $direction, $optional);

        /** @psalm-suppress PossiblyNullArgument */
        return new self(new QueryStructure(
            new ParameterStack(),
            $matches,
            $relationship->name,
        ),
            new BuilderToDSLAdapter());
    }

    /**
     * @param list<string>|string|null $label
     */
    public static function fromNode(array|string $label = null, string|null $name = null, bool $optional = false): self
    {
        $matches = new GraphPattern();
        $relationship = $matches->addMatchingNode($label, $name, $optional);

        /** @psalm-suppress PossiblyNullArgument */
        return new self(new QueryStructure(
            new ParameterStack(),
            $matches,
            $relationship->name,
        ),
            new BuilderToDSLAdapter());
    }
}
