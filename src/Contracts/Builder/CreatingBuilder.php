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

/**
 * Partial builder interface for the CREATE clause.
 *
 * @see https://neo4j.com/docs/cypher-manual/current/clauses/create
 */
interface CreatingBuilder
{
    /**
     * Creates a node with the given label and name.
     *
     * @param non-empty-list<string>|string $label The labels to create. At least one label must be provided.
     * @param string|null                   $name  The name of the node. If provided, the node can be referenced in other parts of the query as a variable.
     *
     * @return $this
     *
     * @see ../../../README.md#variable-usage
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/create/#create-create-a-node-with-a-label
     */
    public function creatingNode(string|array $label, string|null $name = null): static;

    /**
     * Connects two nodes with a relationship.
     *
     * @param string                        $from  the variable name of the node to connect from
     * @param non-empty-list<string>|string $types the types of the relationship
     * @param string                        $end   the variable name of the node to connect to
     * @param string|null                   $name  The name of the relationship. If provided, the relationship can be referenced in other parts of the query as a variable.
     *
     * @return $this
     *
     * @see ../../../README.md#variable-usage
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/create/#create-relationships
     */
    public function creatingConnection(string $from, string|array $types, string $end, string|null $name = null): static;

    /**
     * @param non-empty-list<string>|string $types
     */
    public function creatingRelationship(string $from, string|array $types, string $end, string|null $name = null): static;

    /**
     * Adds a raw match expression. The expression will just be comma-separated and inserted into the CREATE clause.
     *
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/create
     * @see ../../../README.md#variable-usage
     *
     * @return $this
     */
    public function creatingRaw(string $cypher): static;

    /**
     * Creates the patterns with the given values as attributes.
     *
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/create
     * @see ../../../README.md#variable-usage
     * @see ../../../README.md#property-usage
     *
     * @return $this
     */
    public function creating(array $values = []): static;

    /**
     * Batch creates the patterns with the given values.
     *
     * @param list<array<string, mixed>> $rows The rows to create. Each row is an associative array with the keys being the property names and the values being the property values. The rows must all have the same keys.
     *
     * @return $this
     *
     * @see ../../../README.md#variable-usage
     * @see ../../../README.md#property-usage
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/create
     */
    public function batchCreating(array $rows): static;
}
