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

use PhpGraphGroup\CypherQueryBuilder\Common\MapValue;
use PhpGraphGroup\CypherQueryBuilder\Common\Variable;
use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Concerns\StringDecoder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\CreatingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Set\PropertyAssignment;

/**
 * @implements CreatingBuilder
 */
trait CreatesGraphElements
{
    use StringDecoder;
    use HasQueryStructure;

    /**
     * @param non-empty-list<string>|string $label
     */
    public function creatingNode(string|array $label, string|null $name = null): static
    {
        $this->structure->graphPattern->addCreatingNode($label, $name, []);

        return $this;
    }

    /**
     * @param non-empty-list<string>|string $types
     */
    public function creatingConnection(string $from, string|array $types, string $end, string|null $name = null): static
    {
        $this->structure->graphPattern->addCreatingRelationship($from, $end, $types, $name, [], null);

        return $this;
    }

    /**
     * @param non-empty-list<string>|string $types
     */
    public function creatingRelationship(string $from, string|array $types, string $end, string|null $name = null): static
    {
        return $this->creatingConnection($from, $types, $end, $name);
    }

    public function creatingRaw(string $cypher): static
    {
        $this->structure->graphPattern->addCreatingRaw($cypher);

        return $this;
    }

    public function creating(array|string $values = []): static
    {
        $pattern = $this->structure->graphPattern;
        $this->structure->batchCreate = null;

        if (is_string($values)) {
            $pattern->set($values, [], 'create');

            return $this;
        }

        foreach ($values as $key => $value) {
            $property = $this->stringToProperty($key);
            $param = $this->structure->parameters->add($value);

            $pattern->set($property->variable->name, [new PropertyAssignment($property, $param)], 'create');
        }

        if (count($values) === 0) {
            $pattern->set($this->structure->entry->name, [], 'create');
        }

        return $this;
    }

    public function batchCreating(array $rows): static
    {
        $pattern = $this->structure->graphPattern;

        if (count($rows) === 0) {
            $this->structure->batchCreate = null;

            return $this;
        }

        $this->structure->batchCreate = $this->structure->parameters->add($rows);

        $values = $rows[0];

        if (count($values) === 0) {
            $pattern->set($this->structure->entry->name, [], 'create');
        }

        /** @var string $key */
        foreach (array_keys($values) as $key) {
            $property = $this->stringToProperty($key);
            $value = new MapValue(new Variable('toCreate'), $key);

            $pattern->set($property->variable->name, [new PropertyAssignment($property, $value)], 'create');
        }

        return $this;
    }
}
