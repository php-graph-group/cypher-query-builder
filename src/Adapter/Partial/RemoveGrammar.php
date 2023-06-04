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

use PhpGraphGroup\CypherQueryBuilder\Common\Property;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Concerns\TranslatesObjectsToDsl;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PartialGrammar;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use WikibaseSolutions\CypherDSL\Clauses\RawClause;
use WikibaseSolutions\CypherDSL\Query;

class RemoveGrammar implements PartialGrammar
{
    use TranslatesObjectsToDsl;

    public function compile(QueryStructure $structure): iterable
    {
        if (count($structure->remove) === 0) {
            return;
        }

        $toRemove = [];
        foreach ($structure->remove as $remove) {
            if ($remove instanceof RawExpression) {
                $toRemove[] = Query::rawExpression($remove->cypher);
            } elseif ($remove instanceof Property) {
                $toRemove[] = Query::variable($remove->variable->name)->property($remove->name);
            } else {
                $toRemove[] = Query::variable($remove->variable->name)->labeled(...$remove->labels);
            }
        }

        yield new RawClause('REMOVE', implode(',', $this->toSubjectParts($toRemove)));
    }
}
