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

class MergeGrammar implements PartialGrammar
{
    use TranslatesObjectsToDsl;

    public function compile(QueryStructure $structure): iterable
    {
        $toCreate = [];
        $chunks = $structure->graphPattern->chunk('merge');
        if (count($chunks) === 0) {
            return;
        }

        foreach ($chunks as $chunk) {
            $toCreate[] = $this->chunkToDsl($chunk);
        }

        yield new RawClause('MERGE', implode(',', $this->toSubjectParts($toCreate)));

        if (count($structure->onMatch)) {
            $toSet = [];
            foreach ($structure->onMatch as $assignment) {
                $toSet[] = $this->setAssignmentsToDsl($assignment);
            }
            yield new RawClause('ON MATCH SET', implode(',', $this->toSubjectParts($toSet)));
        }

        if (count($structure->onCreate)) {
            $toSet = [];
            foreach ($structure->onCreate as $assignment) {
                $toSet[] = $this->setAssignmentsToDsl($assignment);
            }
            yield new RawClause('ON CREATE SET', implode(',', $this->toSubjectParts($toSet)));
        }
    }
}
