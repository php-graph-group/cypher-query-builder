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

namespace PhpGraphGroup\CypherQueryBuilder\Adapter\Partial;

use Closure;
use PhpGraphGroup\CypherQueryBuilder\Concerns\TranslatesObjectsToDsl;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\WhereBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PartialGrammar;
use PhpGraphGroup\CypherQueryBuilder\GrammarPipeline;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Where\BooleanOperator;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\BinaryBooleanOperator;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\BinaryBooleanPropertyOperator;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\InnerWhereExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\NullBooleanExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\RawBoolean;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\SubQueryCountBooleanExpression;
use PhpGraphGroup\CypherQueryBuilder\Where\Expressions\SubQueryExistsExpression;
use WikibaseSolutions\CypherDSL\Clauses\WhereClause;
use WikibaseSolutions\CypherDSL\Expressions\Parameter;
use WikibaseSolutions\CypherDSL\Expressions\Property;
use WikibaseSolutions\CypherDSL\Expressions\RawExpression;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;

/**
 * @psalm-suppress InternalMethod
 *
 * @psalm-import-type Operator from WhereBuilder
 */
class WhereGrammar implements PartialGrammar
{
    use TranslatesObjectsToDsl;

    /**
     * @param Closure():GrammarPipeline $subQueryPipeline
     */
    public function __construct(
        private readonly Closure $subQueryPipeline
    ) {}

    /**
     * @param Operator $operator
     */
    public function binaryBool(string $operator, RawExpression|Property $left, Parameter|Property $right): BooleanType
    {
        return match ($operator) {
            '<', => $left->lt($right, false),
            '<=' => $left->lte($right, false),
            '=', '==', '===' => $left->equals($right, false),
            '>' => $left->gt($right, false),
            '>=' => $left->gte($right, false),
            '=~', 'LIKE' => $left->regex($right, false),
            '!==', '!=', '<>' => $left->notEquals($right, false),
            'STARTS WITH' => $left->startsWith($right, false),
            'ENDS WITH' => $left->endsWith($right, false),
            'CONTAINS' => $left->contains($right, false),
            'IN' => $left->in($right, false)
        };
    }

    private function chain(BooleanType|null $origin, BooleanType|RawExpression $other, BooleanOperator $op, bool $insertParenthesis): BooleanType
    {
        if ($origin === null) {
            return $other;
        }

        return match ($op) {
            BooleanOperator::OR => $origin->or($other, $insertParenthesis),
            BooleanOperator::XOR => $origin->xor($other, $insertParenthesis),
            BooleanOperator::AND => $origin->and($other, $insertParenthesis),
        };
    }

    /**
     * @param non-empty-list<BinaryBooleanOperator|BinaryBooleanPropertyOperator|InnerWhereExpression|NullBooleanExpression|RawBoolean|SubQueryExistsExpression|SubQueryCountBooleanExpression> $wheres
     */
    private function compileWheres(array $wheres): BooleanType
    {
        $bools = null;
        foreach ($wheres as $where) {
            if ($where instanceof NullBooleanExpression) {
                $prop = Query::variable($where->value->variable->name)
                    ->property($where->value->name);

                if ($where->negate) {
                    $bools = $this->chain($bools, $prop->isNotNull(false), $where->chainingOperator, false);
                } else {
                    $bools = $this->chain($bools, $prop->isNull(false), $where->chainingOperator, false);
                }
            } elseif ($where instanceof InnerWhereExpression) {
                $inner = $this->compileWheres($where->wheres);
                $insertParenthesis = count($where->wheres) > 1;
                if ($where->negate) {
                    $inner = $inner->not();
                    $insertParenthesis = false; // NOT expression already inserts parenthesis
                }

                $bools = $this->chain($bools, $inner, $where->chainingOperator, $insertParenthesis);
            } elseif ($where instanceof BinaryBooleanOperator) {
                $left = Query::variable($where->left->variable->name)->property($where->left->name);
                $right = Query::parameter($where->right->name);

                $bool = $this->binaryBool($where->operator, $left, $right);
                $bools = $this->chain($bools, $bool, $where->chainingOperator, false);
            } elseif ($where instanceof SubQueryCountBooleanExpression) {
                $left = call_user_func($this->subQueryPipeline)->pipe($where->left->getStructure());
                $left = Query::rawExpression('COUNT { '.$left->toQuery().' }');
                $right = Query::parameter($where->right->name);

                $bool = $this->binaryBool($where->operator, $left, $right);
                $bools = $this->chain($bools, $bool, $where->chainingOperator, false);
            } elseif ($where instanceof BinaryBooleanPropertyOperator) {
                $left = Query::variable($where->left->variable->name)->property($where->left->name);
                $right = Query::variable($where->right->variable->name)->property($where->right->name);

                $bool = $this->binaryBool($where->operator, $left, $right);
                $bools = $this->chain($bools, $bool, $where->chainingOperator, false);
            } elseif ($where instanceof RawBoolean) {
                $bool = Query::rawExpression($where->cypher);
                $bools = $this->chain($bools, $bool, $where->chainingOperator, true);
            } else {
                $expression = call_user_func($this->subQueryPipeline)->pipe($where->subQuery->getStructure());
                if ($where->negate) {
                    $bool = Query::rawExpression('NOT EXISTS { '.$expression->toQuery().' }');
                } else {
                    $bool = Query::rawExpression('EXISTS { '.$expression->toQuery().' }');
                }

                $bools = $this->chain($bools, $bool, $where->chainingOperator, false);
            }
        }

        return $bools;
    }

    public function compile(QueryStructure $structure): iterable
    {
        if (count($structure->wheres) === 0) {
            return;
        }

        $clause = new WhereClause();
        yield $clause->addExpression($this->compileWheres($structure->wheres));
    }
}
