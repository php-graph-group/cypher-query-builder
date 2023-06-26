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

/**
 * @internal
 */
class GraphPattern
{
    /**
     * @var list<array{value: PropertyNode|PropertyRelationship|RawExpression, mode: 'match'|'optional-match'|'merge'|'create'}>
     */
    private array $pattern = [];

    private int $anonymousCounter = 0;

    /**
     * @param list<string>|string|null $labels
     */
    public function addMatchingNode(array|string|null $labels, string|null $name, bool $optional): PropertyNode
    {
        return $this->createNode($name, $labels, $optional ? 'optional-match' : 'match');
    }

    /**
     * @param non-empty-list<string>|string $labels
     * @param list<PropertyAssignment>      $properties
     */
    public function addCreatingNode(array|string $labels, string|null $name, array $properties): PropertyNode
    {
        $node = $this->createNode($name, $this->coalesceStrict($labels), 'create');

        $node->properties = $properties;

        return $node;
    }

    /**
     * @param non-empty-list<string>|string $labels
     * @param list<PropertyAssignment>      $properties
     */
    public function addMergingNode(array|string $labels, string|null $name, array $properties): PropertyNode
    {
        $node = $this->createNode($name, $this->coalesceStrict($labels), 'merge');

        $node->properties = $properties;

        return $node;
    }

    /**
     * @param list<PropertyAssignment> $properties
     * @param 'merge'|'create'         $mode
     */
    public function set(string $variable, array $properties, string $mode): void
    {
        foreach ($this->pattern as &$element) {
            $value = $element['value'];
            if ($value instanceof PropertyNode && $value->name->name === $variable) {
                $element['value'] = new PropertyNode(
                    $value->name,
                    $value->labels,
                    [...$value->properties, ...$properties]
                );
                $element['mode'] = $mode;

                return;
            }

            if ($value instanceof PropertyRelationship && $value->name->name === $variable) {
                $element['value'] = new PropertyRelationship(
                    $value->name,
                    $value->left,
                    $value->right,
                    $value->types,
                    [...$value->properties, ...$properties],
                    $value->direction,
                );
                $element['mode'] = $mode;

                return;
            }
        }
    }

    /**
     * @param list<string>|string|null                  $labels
     * @param 'match'|'optional-match'|'merge'|'create' $mode
     */
    private function createNode(string|null $name, array|string|null $labels, string $mode): PropertyNode
    {
        [$labels, $name] = $this->normaliseNameAndLabelOrType($labels, $name, 'node');
        $node = $this->getByNameLoose($name) ?? new PropertyNode(new Variable($name), $labels, []);
        if ($node instanceof PropertyRelationship) {
            throw new RuntimeException('Cannot use a relationship as a node');
        }

        $this->pattern[] = ['value' => $node, 'mode' => $mode];

        return $node;
    }

    /**
     * @param list<string>|string|null                  $types
     * @param 'match'|'merge'|'create'|'optional-match' $mode
     */
    private function createRelationship(string|null $left, string|null $right, array|string|null $types, string|null $name, string $mode, Direction|null $direction): PropertyRelationship
    {
        [$leftLabels, $left] = $this->normaliseNameAndLabelOrType(null, $left, 'node');
        if (count($leftLabels) > 0) {
            $this->createNode($left, $leftLabels, $mode);
        }

        [$rightLabels, $right] = $this->normaliseNameAndLabelOrType(null, $right, 'node');
        if (count($rightLabels) > 0) {
            $this->createNode($right, $rightLabels, $mode);
        }

        [$types, $name] = $this->normaliseNameAndLabelOrType($types, $name, 'relationship', $direction);

        $relationship = $this->getByNameLoose($name) ?? new PropertyRelationship(
            new Variable($name),
            new Variable($left),
            new Variable($right),
            $types,
            [],
            $direction ?? Direction::LEFT_TO_RIGHT
        );
        if ($relationship instanceof PropertyNode) {
            throw new RuntimeException('Cannot use a relationship as a node');
        }

        $this->pattern[] = ['value' => $relationship, 'mode' => $mode];

        return $relationship;
    }

    /**
     * @param list<string>|string|null $types
     */
    public function addMatchingRelationship(string|null $left, string|null $right, array|string|null $types, string|null $name, Direction|null $direction, bool $optional): PropertyRelationship
    {
        return $this->createRelationship($left, $right, $types, $name, $optional ? 'optional-match' : 'match', $direction);
    }

    /**
     * @param non-empty-list<string>|string|null $types
     * @param list<PropertyAssignment>           $properties
     */
    public function addCreatingRelationship(string $left, string $right, array|string|null $types, string|null $name, array $properties, Direction|null $direction): PropertyRelationship
    {
        $relationship = $this->createRelationship($left, $right, $this->coalesceStrict($types), $name, 'create', $direction);

        $relationship->properties = $properties;

        return $relationship;
    }

    /**
     * @param non-empty-list<string>|string|null $types
     * @param list<PropertyAssignment>           $properties
     */
    public function addMergingRelationship(string $start, string $end, array|string|null $types, string|null $name, array $properties, Direction|null $direction): PropertyRelationship
    {
        $relationship = $this->createRelationship($start, $end, $this->coalesceStrict($types), $name, 'merge', $direction);

        $relationship->properties = $properties;

        return $relationship;
    }

    public function getByNameLoose(string $name): PropertyNode|PropertyRelationship|null
    {
        foreach ($this->pattern as $part) {
            if (!$part['value'] instanceof RawExpression && $part['value']->name->name === $name) {
                return $part['value'];
            }
        }

        return null;
    }

    public function addMatchingRaw(string $cypher, bool $optional = false): void
    {
        $this->pattern[] = ['value' => new RawExpression($cypher), 'mode' => 'match'];
    }

    /**
     * @param 'match'|'matchOptional'|'matchStrict'|'merge'|'create' $mode
     *
     * @return list<PropertyNode|PropertyRelationship|RawExpression>
     */
    public function chunk(string $mode): array
    {
        $matches = array_values(array_filter(
            $this->pattern,
            static fn ($x) => $x['mode'] === 'match' || $x['mode'] === 'optional-match'
        ));

        return $this->order(
            $this->mapValue(match ($mode) {
                'match' => $matches,
                'matchOptional' => array_values(array_filter($matches, fn ($x) => $x['mode'] === 'optional-match')),
                'matchStrict' => array_values(array_filter($matches, fn ($x) => $x['mode'] === 'match')),
                'merge' => array_values(array_filter($this->pattern, static fn ($x) => $x['mode'] === 'merge')),
                'create' => array_values(array_filter($this->pattern, static fn ($x) => $x['mode'] === 'create')),
            })
        );
    }

    private function setOrder(RawExpression|PropertyNode|PropertyRelationship $x): int
    {
        if ($x instanceof RawExpression) {
            return 0;
        }

        if ($x instanceof PropertyNode) {
            return 1;
        }

        return 2;
    }

    /**
     * @param list<RawExpression|PropertyNode|PropertyRelationship> $elements
     *
     * @return list<RawExpression|PropertyNode|PropertyRelationship>
     */
    private function order(array $elements): array
    {
        usort($elements, fn ($a, $b) => $this->setOrder($a) <=> $this->setOrder($b));

        return $elements;
    }

    public function addCreatingRaw(string $cypher): void
    {
        $this->pattern[] = ['value' => new RawExpression($cypher), 'mode' => 'create'];
    }

    public function addMergingRaw(string $cypher): void
    {
        $this->pattern[] = ['value' => new RawExpression($cypher), 'mode' => 'merge'];
    }

    /**
     * @param list<array{value: PropertyNode|PropertyRelationship|RawExpression, mode: 'match'|'create'|'merge'|'optional-match'}> $param
     *
     * @return list<PropertyNode|PropertyRelationship|RawExpression>
     */
    private function mapValue(array $param): array
    {
        return array_map(static fn ($x) => $x['value'], $param);
    }

    /**
     * @param list<string>|string|null $typeOrLabels
     * @param 'node'|'relationship'    $target
     *
     * @return array{0: list<string>, 1: string}
     */
    private function normaliseNameAndLabelOrType(array|string|null $typeOrLabels, string|null $name, string $target, Direction &$direction = null): array
    {
        $decoded = $this->decode($typeOrLabels, $target, $direction, $name);

        $first = null;
        $typeOrLabels = [];
        foreach ($decoded[0] as $x) {
            $x = trim($x);
            if ($x !== '') {
                $first ??= $x;
                $typeOrLabels[] = $x;
            }
        }

        $name = trim($decoded[1]);
        if ($name === '') {
            if ($first) {
                $name = $first;
            } else {
                $name = $this->anonymousName();
            }
        }

        return [$typeOrLabels, $name];
    }

    /**
     * @param string|list<string>|null $labelOrType
     *
     * @return list<string>
     */
    private function coalesce(string|array|null $labelOrType): array
    {
        if ($labelOrType === null) {
            return [];
        }

        if (is_string($labelOrType)) {
            return [$labelOrType];
        }

        return $labelOrType;
    }

    /**
     * @param string|list<string>|null $labelOrType
     *
     * @return non-empty-list<string>
     */
    private function coalesceStrict(string|array|null $labelOrType): array
    {
        $tbr = $this->coalesce($labelOrType);

        if (count($tbr) === 0) {
            throw new RuntimeException('Received empty label or type list');
        }

        return $tbr;
    }

    private function anonymousName(): string
    {
        $tbr = 'anon'.$this->anonymousCounter;
        ++$this->anonymousCounter;

        return $tbr;
    }

    /**
     * @param list<string> $types
     */
    public function extractDirection(?Direction &$direction, array &$types): void
    {
        if ($direction === null && count($types) > 0) {
            if (str_contains($types[0], '<')) {
                $types[0] = str_replace('<', '', $types[0]);
                $direction = Direction::RIGHT_TO_LEFT;
            }
            if (str_contains($types[0], '>')) {
                $types[0] = str_replace('>', '', $types[0]);
                $direction = $direction === Direction::RIGHT_TO_LEFT ? Direction::ANY : Direction::LEFT_TO_RIGHT;
            }
        }
    }

    /**
     * @param list<string>|string|null $typeOrLabels
     *
     * @return array{0: list<string>, 1: string}
     */
    public function decode(array|string|null $typeOrLabels, string $target, ?Direction &$direction, ?string $name): array
    {
        $typeOrLabels = $this->coalesce($typeOrLabels);
        if ($target === 'relationship') {
            $this->extractDirection($direction, $typeOrLabels);
        }

        // If there are no types or labels we will see if the name contains a label definition
        // This definition is denoted by the use of a colon eg: myNodeNode:MyLabel
        if (count($typeOrLabels) === 0) {
            if (str_contains($name ?? '', ':')) {
                /** @psalm-suppress PossiblyUndefinedArrayOffset */
                [$name, $typeOrLabel] = explode(':', $name ?? '', 2);
                $typeOrLabels[] = $typeOrLabel;
            }

            return [$typeOrLabels, $name ?? $this->anonymousName()];
        }

        // Having a name and labels defined separately makes it so no string codec will be required
        if ($name !== null) {
            return [$typeOrLabels, $name];
        }

        // In all other cases we now there is no name defined and at least one label.
        // We will use the first label to detect any encoding to extract the name from it.
        // If there is no encoding we wil use the label to generate a type or name.
        if (str_contains($typeOrLabels[0], ':')) {
            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$name, $typeOrLabel] = explode(':', $typeOrLabels[0], 2);
            $typeOrLabels[0] = $typeOrLabel;
        } else {
            $name = $typeOrLabels[0];
        }

        return [$typeOrLabels, $name];
    }
}
