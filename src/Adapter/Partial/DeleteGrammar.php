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

use PhpGraphGroup\CypherQueryBuilder\Contracts\PartialGrammar;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use WikibaseSolutions\CypherDSL\Clauses\DeleteClause;
use WikibaseSolutions\CypherDSL\Query;

class DeleteGrammar implements PartialGrammar
{
    public function compile(QueryStructure $structure): iterable
    {
        if (count($structure->delete) === 0) {
            return;
        }

        $clause = new DeleteClause();
        foreach ($structure->delete as $delete) {
            $clause->addStructure(Query::variable($delete->name));
        }

        $clause->setDetach($structure->forceDelete);

        yield $clause;
    }
}
