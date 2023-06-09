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

use PhpGraphGroup\CypherQueryBuilder\Common\Alias;
use PhpGraphGroup\CypherQueryBuilder\Common\Direction;
use PhpGraphGroup\CypherQueryBuilder\Common\FunctionCall;
use PhpGraphGroup\CypherQueryBuilder\Common\MapValue;
use PhpGraphGroup\CypherQueryBuilder\Common\Parameter;
use PhpGraphGroup\CypherQueryBuilder\Common\Property;
use PhpGraphGroup\CypherQueryBuilder\Common\PropertyNode;
use PhpGraphGroup\CypherQueryBuilder\Common\PropertyRelationship;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Common\Variable;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Set\LabelAssignment;
use PhpGraphGroup\CypherQueryBuilder\Set\PropertyAssignment;
use WikibaseSolutions\CypherDSL\Expressions\Label;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Patterns\Path;
use WikibaseSolutions\CypherDSL\Patterns\Relationship;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\QueryConvertible;
use WikibaseSolutions\CypherDSL\Syntax\PropertyReplacement;

trait TranslatesObjectsToDsl
{
    /**
     * @template T of Relationship|Node
     *
     * @param T $dsl
     *
     * @return T
     */
    private function assignNameAndProperties(PropertyRelationship|PropertyNode $value, Relationship|Node $dsl): Relationship|Node
    {
        $dsl->withVariable($value->name->name);

        if (count($value->properties)) {
            $properties = [];
            foreach ($value->properties as $assignment) {
                if ($assignment->value instanceof Parameter) {
                    $properties[$assignment->property->name] = Query::parameter($assignment->value->name);
                } elseif ($assignment->value instanceof RawExpression) {
                    $properties[$assignment->property->name] = Query::rawExpression($assignment->value->cypher);
                } else {
                    $properties[$assignment->property->name] = $this->mapValueToAssignmentValue($assignment->value);
                }
            }

            $dsl->withProperties($properties);
        }

        return $dsl;
    }

    private function returnToDsl(QueryStructure $structure, Property|Variable|RawExpression|FunctionCall|Alias $return): \WikibaseSolutions\CypherDSL\Expressions\Property|\WikibaseSolutions\CypherDSL\Expressions\Variable|\WikibaseSolutions\CypherDSL\Expressions\RawExpression|\WikibaseSolutions\CypherDSL\Syntax\Alias
    {
        if ($return instanceof Property) {
            return Query::variable($return->variable->name)->property($return->name);
        }

        if ($return instanceof Variable) {
            return Query::variable($return->name);
        }

        if ($return instanceof RawExpression) {
            return Query::rawExpression($return->cypher);
        }

        if ($return instanceof FunctionCall) {
            $parts = [];
            if (count($return->arguments) && $structure->distinct) {
                $parts[] = Query::rawExpression('DISTINCT');
            }
            foreach ($return->arguments as $argument) {
                if ($argument instanceof RawExpression) {
                    $parts[] = Query::rawExpression($argument->cypher);
                } elseif ($argument instanceof Property) {
                    $parts[] = Query::variable($argument->variable->name)->property($argument->name);
                } else {
                    /** @psalm-suppress UndefinedPropertyFetch */
                    $parts[] = Query::parameter($argument->name);
                }
            }
            $cypher = sprintf('%s(%s)', $return->function, implode(',', $this->toSubjectParts($parts)));

            return Query::rawExpression($cypher);
        }

        $tbr = $this->returnToDsl($structure, $return->expression);
        if ($tbr instanceof \WikibaseSolutions\CypherDSL\Syntax\Alias) {
            return $tbr;
        }

        return $tbr->alias($return->alias);
    }

    private function setAssignmentsToDsl(LabelAssignment|PropertyAssignment|RawExpression $assignment): Label|PropertyReplacement|\WikibaseSolutions\CypherDSL\Expressions\RawExpression
    {
        if ($assignment instanceof LabelAssignment) {
            return Query::variable($assignment->variable->name)
                ->labeled(...$assignment->labels);
        }

        if ($assignment instanceof PropertyAssignment) {
            return Query::variable($assignment->property->variable->name)
                ->property($assignment->property->name)
                ->replaceWith(Query::parameter($assignment->value->name));
        }

        return Query::rawExpression($assignment->cypher);
    }

    private function mapValueToAssignmentValue(MapValue $map): \WikibaseSolutions\CypherDSL\Expressions\RawExpression
    {
        return Query::rawExpression(sprintf(
            '%s[%s]',
            Query::variable($map->variable->name)->toQuery(),
            Query::string($map->property)->toQuery()
        ));
    }

    private function propertyNodeToDsl(PropertyNode $propertyNode): Node
    {
        $node = Query::node()->withLabels($propertyNode->labels);

        return $this->assignNameAndProperties($propertyNode, $node);
    }

    private function propertyRelationshipToDsl(PropertyRelationship $propertyRelationship): Path
    {
        $relationship = Query::relationship(match ($propertyRelationship->direction) {
            Direction::RIGHT_TO_LEFT => Relationship::DIR_LEFT,
            Direction::LEFT_TO_RIGHT => Relationship::DIR_RIGHT,
            Direction::ANY => Relationship::DIR_UNI,
        })->withTypes($propertyRelationship->types);

        $relationship = $this->assignNameAndProperties($propertyRelationship, $relationship);

        $start = Query::node();
        $end = Query::node();
        $start = $start->withVariable($propertyRelationship->left->name);
        $end = $end->withVariable($propertyRelationship->right->name);

        return $start->relationship($relationship, $end);
    }

    /**
     * @param list<QueryConvertible> $queryConvertibles
     *
     * @return list<string>
     */
    private function toSubjectParts(array $queryConvertibles): array
    {
        /** @psalm-suppress InternalMethod */
        return array_map(static fn (QueryConvertible $c) => $c->toQuery(), $queryConvertibles);
    }

    private function chunkToDsl(PropertyNode|RawExpression|PropertyRelationship $chunk): Path|\WikibaseSolutions\CypherDSL\Expressions\RawExpression|Node
    {
        if ($chunk instanceof RawExpression) {
            return Query::rawExpression($chunk->cypher);
        }

        if ($chunk instanceof PropertyNode) {
            return $this->propertyNodeToDsl($chunk);
        }

        return $this->propertyRelationshipToDsl($chunk);
    }
}
