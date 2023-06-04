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

namespace PhpGraphGroup\CypherQueryBuilder\Concerns\Builder;

use PhpGraphGroup\CypherQueryBuilder\Concerns\CoalescesTypesAndLabelLists;
use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\MatchingBuilder;

/**
 * @implements MatchingBuilder
 */
trait MatchesGraphs
{
    use HasQueryStructure;
    use CoalescesTypesAndLabelLists;

    /**
     * @param string|list<string>|null $label
     *
     * @return $this
     */
    public function matchingNode(string|array|null $label = null, string|null $name = null, bool $optional = false): static
    {
        $this->structure->graphPattern->addMatchingNode($this->coalesce($label), $name, $optional);

        return $this;
    }

    public function matchingRaw(string $cypher, bool $optional = false): static
    {
        $this->structure->graphPattern->addMatchingRaw($cypher, $optional);

        return $this;
    }

    /**
     * @param string|list<string>|null $type
     *
     * @return $this
     */
    public function matchingRelationship(string|null $from = null, string|array|null $type = null, string|null $end = null, string|null $name = null, bool $optional = false): static
    {
        if ($type === null) {
            $type = [];
        } elseif (is_string($type)) {
            $type = [$type];
        }

        $this->structure->graphPattern->addMatchingRelationship($from, $end, $type, $name, $optional);

        return $this;
    }
}