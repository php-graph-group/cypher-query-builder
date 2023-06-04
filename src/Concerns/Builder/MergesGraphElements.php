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

use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Concerns\StringDecoder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\MergingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Set\PropertyAssignment;

/**
 * @implements MergingBuilder
 */
trait MergesGraphElements
{
    use HasQueryStructure;
    use StringDecoder;

    /**
     * @param string|non-empty-list<string> $label
     */
    public function mergingNode(string|array $label, string|null $name = null): static
    {
        $this->structure->graphPattern->setMergingNode($this->coalesceStrict($label), $name, []);

        return $this;
    }

    /**
     * @param non-empty-list<string>|string $types
     */
    public function mergingConnection(string $from, string|array $types, string $end, string|null $name = null): static
    {
        $this->structure->graphPattern->setMergingRelationship($from, $end, $this->coalesceStrict($types), $name, []);

        return $this;
    }

    public function mergingRaw(string $cypher): static
    {
        $this->structure->graphPattern->addMergingRaw($cypher);

        return $this;
    }

    public function merging(array $values = []): static
    {
        $pattern = $this->structure->graphPattern;

        foreach ($values as $key => $value) {
            $property = $this->stringToProperty($key);
            $param = $this->structure->parameters->add($value);

            $pattern->set($property->variable->name, [new PropertyAssignment($property, $param)], 'merge');
        }

        return $this;
    }

    public function onMatching(array $values = []): static
    {
        foreach ($values as $key => $value) {
            $property = $this->stringToProperty($key);
            $param = $this->structure->parameters->add($value);

            $this->structure->onMatch[] = new PropertyAssignment($property, $param);
        }

        return $this;
    }

    public function onCreating(array $values = []): static
    {
        foreach ($values as $key => $value) {
            $property = $this->stringToProperty($key);
            $param = $this->structure->parameters->add($value);

            $this->structure->onCreate[] = new PropertyAssignment($property, $param);
        }

        return $this;
    }
}
