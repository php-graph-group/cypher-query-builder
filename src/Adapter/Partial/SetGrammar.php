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

use PhpGraphGroup\CypherQueryBuilder\Concerns\TranslatesObjectsToDsl;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PartialGrammar;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use WikibaseSolutions\CypherDSL\Clauses\RawClause;

class SetGrammar implements PartialGrammar
{
    use TranslatesObjectsToDsl;

    public function compile(QueryStructure $structure): iterable
    {
        if (count($structure->set) === 0) {
            return;
        }

        $toSet = [];
        foreach ($structure->set as $assignment) {
            $toSet[] = $this->setAssignmentsToDsl($assignment);
        }

        yield new RawClause('SET', implode(',', $this->toSubjectParts($toSet)));
    }
}
