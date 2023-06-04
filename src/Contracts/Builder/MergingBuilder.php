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

interface MergingBuilder
{
    /**
     * Matches a node.
     *
     * @param non-empty-list<string>|string $label
     */
    public function mergingNode(string|array $label, string|null $name = null): static;

    /**
     * Connects two nodes with a relationship.
     *
     * @param non-empty-list<string>|string $types
     */
    public function mergingConnection(string $from, string|array $types, string $end, string|null $name = null): static;

    /**
     * Adds a raw match expression.
     */
    public function mergingRaw(string $cypher): static;

    public function merging(array $values = []): static;

    public function onMatching(array $values = []): static;

    public function onCreating(array $values = []): static;
}
