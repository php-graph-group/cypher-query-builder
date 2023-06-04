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

use PhpGraphGroup\CypherQueryBuilder\Adapter\BuilderToDSLAdapter;
use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;
use PhpGraphGroup\CypherQueryBuilder\Common\ParameterStack;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;

trait Builder
{
    /**
     * @param list<string>|string|null $label
     */
    public static function from(array|string|null $label = null, string|null $name = null): self
    {
        $matches = new GraphPattern();

        if ($label === null) {
            $label = [];
            if ($name === null) {
                $name = 'n';
            }
        }

        if (is_string($label)) {
            $label = [$label];
        }

        $node = $matches->addMatchingNode($label, $name, false);

        /** @psalm-suppress PossiblyNullArgument */
        return new self(new QueryStructure(
            new ParameterStack(),
            $matches,
            $node->name,
        ),
            new BuilderToDSLAdapter());
    }

    /**
     * @param list<string>|string|null $type
     */
    public static function fromRelationship(array|string|null $type = null, string|null $name = null): self
    {
        $matches = new GraphPattern();

        if ($type === null) {
            $type = [];
            if ($name === null) {
                $name = 'r';
            }
        }
        if (is_string($type)) {
            $type = [$type];
        }

        $relationship = $matches->addMatchingRelationship(null, null, $type, $name);

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
    public static function fromNode(array|string $label = null, string|null $name = null): self
    {
        return self::from($label, $name);
    }
}
