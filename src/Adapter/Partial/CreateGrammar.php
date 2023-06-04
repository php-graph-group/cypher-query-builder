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
use WikibaseSolutions\CypherDSL\Query;

class CreateGrammar implements PartialGrammar
{
    use TranslatesObjectsToDsl;

    public function compile(QueryStructure $structure): iterable
    {
        $chunks = $structure->graphPattern->chunk('create');
        if (count($chunks) === 0) {
            return;
        }

        if ($structure->batchCreate) {
            yield new RawClause('UNWIND', sprintf('%s AS toCreate', Query::parameter($structure->batchCreate->name)->toQuery()));
        }

        $toCreate = [];
        foreach ($chunks as $chunk) {
            $toCreate[] = $this->chunkToDsl($chunk);
        }

        yield new RawClause('CREATE', implode(',', $this->toSubjectParts($toCreate)));
    }
}
