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

use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Concerns\TranslatesObjectsToDsl;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PartialGrammar;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use WikibaseSolutions\CypherDSL\Clauses\LimitClause;
use WikibaseSolutions\CypherDSL\Clauses\RawClause;
use WikibaseSolutions\CypherDSL\Clauses\ReturnClause;
use WikibaseSolutions\CypherDSL\Clauses\SkipClause;
use WikibaseSolutions\CypherDSL\Query;

class ReturnGrammar implements PartialGrammar
{
    use TranslatesObjectsToDsl;

    public function compile(QueryStructure $structure): iterable
    {
        $clause = new ReturnClause();
        foreach ($structure->return as $return) {
            $clause->addColumn($this->returnToDsl($structure, $return));
        }

        $clause->setDistinct($structure->distinct);

        yield $clause;

        if (count($structure->orderBys) > 0) {
            $clause = 'ORDER BY';

            $properties = [];
            foreach ($structure->orderBys as $orderBy) {
                if ($orderBy instanceof RawExpression) {
                    $properties[] = Query::rawExpression($orderBy->cypher);
                } else {
                    $properties[] = Query::variable($orderBy->variable->name)->property($orderBy->name);
                }
            }

            $subject = implode(',', $this->toSubjectParts($properties));
            if ($structure->orderByDirection === 'DESC') {
                $subject .= ' DESC';
            }

            yield new RawClause($clause, $subject);
        }

        if ($structure->skip) {
            yield (new SkipClause())->setSkip($structure->skip);
        }

        if ($structure->limit !== null) {
            yield (new LimitClause())->setLimit($structure->limit);
        }
    }
}
