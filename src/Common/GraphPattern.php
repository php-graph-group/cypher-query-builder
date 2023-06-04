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

namespace PhpGraphGroup\CypherQueryBuilder\Common;

use PhpGraphGroup\CypherQueryBuilder\Set\PropertyAssignment;
use RuntimeException;

class GraphPattern
{
    /**
     * @var list<array{value: PropertyNode|PropertyRelationship|RawExpression, optional: bool, mode: 'match'|'merge'|'create'}>
     */
    private array $pattern = [];

    /**
     * @param list<string> $labels
     */
    public function addMatchingNode(array $labels, string|null $name, bool $optional): PropertyNode
    {
        return $this->createNode($name, $labels, 'match', [], $optional);
    }

    /**
     * @param non-empty-list<string>   $labels
     * @param list<PropertyAssignment> $properties
     */
    public function addCreatingNode(array $labels, string|null $name, array $properties): PropertyNode
    {
        return $this->createNode($name, $labels, 'create', $properties, false);
    }

    /**
     * @param non-empty-list<string>   $labels
     * @param list<PropertyAssignment> $properties
     */
    public function setMergingNode(array $labels, string|null $name, array $properties): PropertyNode
    {
        return $this->createNode($name, $labels, 'merge', $properties, false);
    }

    /**
     * @param list<PropertyAssignment> $properties
     * @param 'merge'|'create'         $mode
     */
    public function set(string $variable, array $properties, string $mode): void
    {
        foreach ($this->pattern as &$element) {
            $value = $element['value'];
            if ($value instanceof PropertyNode && $value->name?->name === $variable) {
                $element['value'] = new PropertyNode(
                    $value->name,
                    $value->labels,
                    [...$value->properties, ...$properties]
                );
                $element['mode'] = $mode;

                return;
            }

            if ($value instanceof PropertyRelationship && $value->name?->name === $variable) {
                $element['value'] = new PropertyRelationship(
                    $value->name,
                    $value->start,
                    $value->end,
                    $value->types,
                    [...$value->properties, ...$properties]
                );
                $element['mode'] = $mode;

                return;
            }
        }
    }

    /**
     * @param list<string>             $labels
     * @param 'match'|'merge'|'create' $mode
     * @param list<PropertyAssignment> $properties
     */
    private function createNode(string|null $name, array $labels, string $mode, array $properties, bool $optional): PropertyNode
    {
        [$labels, $name] = $this->normaliseNameAndLabelOrType($labels, $name, 'node');
        if ($name !== null && ($node = $this->getByNameLoose($name, $i)) !== null && $i !== null) {
            $properties = [...$node->properties, ...$properties];

            array_splice($this->pattern, $i, 1);
        }

        $node = new PropertyNode(
            $name === null ? null : new Variable($name),
            $labels,
            $properties
        );

        $this->pattern[] = ['value' => $node, 'optional' => $optional, 'mode' => $mode];

        return $node;
    }

    /**
     * @param list<string>             $types
     * @param 'match'|'merge'|'create' $mode
     * @param list<PropertyAssignment> $properties
     */
    private function createRelationship(string|null $start, string|null $end, array $types, string|null $name, string $mode, array $properties, bool $optional): PropertyRelationship
    {
        if ($start && str_contains($start, ':')) {
            [$startLabels, $start] = $this->normaliseNameAndLabelOrType([$start], null, 'node');
            if (count($startLabels) > 0) {
                $this->createNode($start, $startLabels, $mode, [], false);
            }
        }

        if ($end && str_contains($end, ':')) {
            [$endLabels, $end] = $this->normaliseNameAndLabelOrType([$end], null, 'node');
            if (count($endLabels) > 0) {
                $this->createNode($end, $endLabels, $mode, [], false);
            }
        }

        [$types, $name] = $this->normaliseNameAndLabelOrType($types, $name, 'relationship');
        if ($name !== null && ($relationship = $this->getByNameLoose($name, $i)) !== null && $i !== null) {
            $properties = [...$relationship->properties, ...$properties];

            array_splice($this->pattern, $i, 1);
        }

        $relationship = new PropertyRelationship(
            $name === null ? null : new Variable($name),
            $start === null ? null : new Variable($start),
            $end === null ? null : new Variable($end),
            $types,
            $properties
        );

        $this->pattern[] = ['value' => $relationship, 'optional' => $optional, 'mode' => $mode];

        return $relationship;
    }

    /**
     * @param list<string> $types
     */
    public function addMatchingRelationship(string|null $start, string|null $end, array $types, string $name = null, bool $optional = false): PropertyRelationship
    {
        return $this->createRelationship($start, $end, $types, $name, 'match', [], $optional);
    }

    /**
     * @param non-empty-list<string>   $types
     * @param list<PropertyAssignment> $properties
     */
    public function addCreatingRelationship(string $start, string $end, array $types, string|null $name, array $properties): PropertyRelationship
    {
        return $this->createRelationship($start, $end, $types, $name, 'create', $properties, false);
    }

    /**
     * @param non-empty-list<string>   $types
     * @param list<PropertyAssignment> $properties
     */
    public function setMergingRelationship(string $start, string $end, array $types, string|null $name, array $properties): PropertyRelationship
    {
        return $this->createRelationship($start, $end, $types, $name, 'merge', $properties, false);
    }

    public function getByNameLoose(string $name, int &$index = null): PropertyNode|PropertyRelationship|null
    {
        foreach ($this->pattern as $i => $part) {
            if (!$part['value'] instanceof RawExpression && $part['value']->name?->name === $name) {
                $index = $i;

                return $part['value'];
            }
        }

        return null;
    }

    public function getByName(string $name): PropertyNode|PropertyRelationship
    {
        return $this->getByNameLoose($name) ?? throw new RuntimeException(sprintf('No node or graph element with name "%s" found', $name));
    }

    public function addMatchingRaw(string $cypher, bool $optional = false): void
    {
        $this->pattern[] = ['value' => new RawExpression($cypher), 'optional' => $optional, 'mode' => 'match'];
    }

    /**
     * @param 'match'|'matchOptional'|'matchStrict'|'merge'|'create' $mode
     *
     * @return list<PropertyNode|PropertyRelationship|RawExpression>
     */
    public function chunk(string $mode): array
    {
        $matches = array_values(array_filter($this->pattern, static fn ($x) => $x['mode'] === 'match'));

        return $this->order(
            $this->map(match ($mode) {
                'match' => $matches,
                'matchOptional' => array_values(array_filter($matches, fn ($x) => $x['optional'])),
                'matchStrict' => array_values(array_filter($matches, fn ($x) => !$x['optional'])),
                'merge' => array_values(array_filter($this->pattern, static fn ($x) => $x['mode'] === 'merge')),
                'create' => array_values(array_filter($this->pattern, static fn ($x) => $x['mode'] === 'create')),
            })
        );
    }

    /**
     * @param list<RawExpression|PropertyNode|PropertyRelationship> $elements
     *
     * @return list<RawExpression|PropertyNode|PropertyRelationship>
     */
    private function order(array $elements): array
    {
        usort($elements, static function ($a, $b) {
            // raw expressions always come first
            if ($a instanceof RawExpression) {
                return -1;
            }

            // property relationships always come last
            if ($a instanceof PropertyRelationship) {
                return 1;
            }

            // property nodes come after raw expressions
            if ($b instanceof RawExpression) {
                return 1;
            }

            // but before property relationships
            return 0;
        });

        return $elements;
    }

    public function addCreatingRaw(string $cypher): void
    {
        $this->pattern[] = ['value' => new RawExpression($cypher), 'optional' => false, 'mode' => 'create'];
    }

    public function addMergingRaw(string $cypher): void
    {
        $this->pattern[] = ['value' => new RawExpression($cypher), 'optional' => false, 'mode' => 'merge'];
    }

    /**
     * @param list<array{value: PropertyNode|PropertyRelationship|RawExpression, mode: 'match'|'create'|'merge', optional: bool}> $param
     *
     * @return list<PropertyNode|PropertyRelationship|RawExpression>
     */
    private function map(array $param): array
    {
        return array_map(static fn ($x) => $x['value'], $param);
    }

    /**
     * @param list<string>          $typeOrLabels
     * @param 'node'|'relationship' $target
     *
     * @return array{0: list<string>, 1: string|null}
     */
    private function normaliseNameAndLabelOrType(array $typeOrLabels, string|null $name, string $target): array
    {
        if (count($typeOrLabels) === 0) {
            return [[], $name];
        }

        $typeOrLabel = $typeOrLabels[0];
        if ($typeOrLabel !== '') {
            if ($name === null && str_contains($typeOrLabel, ':')) {
                /** @psalm-suppress PossiblyUndefinedArrayOffset */
                [$name, $typeOrLabel] = explode(':', $typeOrLabel, 2);

                $name = $name === '' ? null : $name;
                $typeOrLabel = empty($typeOrLabel) ? null : $typeOrLabel;
            }
        }

        if ($name === null && $typeOrLabel !== null) {
            $name = ($target === 'node') ? $this->nameFromLabel($typeOrLabel) : $this->nameFromType($typeOrLabel);
        }

        if ($typeOrLabel === null) {
            array_splice($typeOrLabels, 0, 1);
        } else {
            $typeOrLabels[0] = $typeOrLabel;
        }

        return [$typeOrLabels, $name];
    }

    /**
     * Generates a name based on the provided type(s). The types are assumed to be in SCREAMING_CASE.
     */
    private function nameFromType(string|null $type): string|null
    {
        if ($type === null) {
            return null;
        }

        $typenameParts = explode('_', $type);
        $lowercaseParts = array_map(strtolower(...), $typenameParts);
        $pascalCasedParts = array_map(ucfirst(...), $lowercaseParts);
        $pascalCasedName = implode('', $pascalCasedParts);

        return lcfirst($pascalCasedName);
    }

    /**
     * Generates a name from the provided label(s). The labels are assumed to be in PascalCase.
     */
    private function nameFromLabel(string|null $label): string|null
    {
        if ($label === null) {
            return null;
        }

        return lcfirst($label);
    }
}
