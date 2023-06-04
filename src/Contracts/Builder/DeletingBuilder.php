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

interface DeletingBuilder
{
    /**
     * Deletes the nodes and relationships with the given variable names.
     *
     * @see https://neo4j.com/docs/cypher-manual/current/clauses/delete/
     *
     * @note This method adds the variables to the list to be deleted and thus allows multiple calls of itself without overriding the variables.
     *
     * @return $this
     */
    public function deleting(string ...$variables): static;

    /**
     * Forces the deletion of the nodes and relationships, even if they are attached to other nodes. You can optionally provide the nodes and relationships to be deleted directly instead of calling self::deleting().
     *
     * @return $this
     *
     * @see DeletingBuilder::deleting()
     */
    public function forcingDeletion(string ...$variables): static;
}
