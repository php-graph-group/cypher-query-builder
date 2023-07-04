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

namespace PhpGraphGroup\CypherQueryBuilder\Contracts\Builder;

use PhpGraphGroup\CypherQueryBuilder\Common\Direction;

interface MatchingBuilder
{
    /**
     * Matches a node.
     *
     * @param string|list<string>|null $label    The label of the node can also be represented as a combination of name and labels (eg. u:User, p:Person:User)
     * @param string|null              $name     The name of the node. The name overrides the name that may be given in the label. If no name is given in both the name and label, the name will be generated from the label by bringing it to lower case (eg. label User -> name user)
     * @param bool                     $optional If the node is optional
     */
    public function matchingNode(string|array $label = null, string $name = null, bool $optional = false): static;

    /**
     * Adds a raw match expression.
     */
    public function matchingRaw(string $cypher, bool $optional = false): static;

    /**
     * Connects two nodes with a relationship.
     *
     * @param string|list<string>|null $type
     */
    public function matchingRelationship(string $from = null, string|array $type = null, string $end = null, string $name = null, Direction $direction = Direction::LEFT_TO_RIGHT, bool $optional = false): static;
}
