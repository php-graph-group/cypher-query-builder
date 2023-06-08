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

class MatchGrammar implements PartialGrammar
{
    use TranslatesObjectsToDsl;

    public function __construct(
        private readonly bool $strict
    ) {}

    public function compile(QueryStructure $structure): iterable
    {
        $matches = [];
        $strictMatches = $structure->graphPattern->chunk('matchStrict');
        foreach ($strictMatches as $match) {
            $matches[] = $this->chunkToDsl($match);
        }

        yield new RawClause('MATCH', implode(',', $this->toSubjectParts($matches)));

        if ($this->strict === false) {
            foreach ($structure->graphPattern->chunk('matchOptional') as $match) {
                yield new RawClause('OPTIONAL MATCH', $this->chunkToDsl($match)->toQuery());
            }
        }
    }
}
