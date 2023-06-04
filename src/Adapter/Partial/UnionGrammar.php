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
use PhpGraphGroup\CypherQueryBuilder\Contracts\PartialGrammar;
use PhpGraphGroup\CypherQueryBuilder\GrammarPipeline;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use WikibaseSolutions\CypherDSL\Clauses\UnionClause;

class UnionGrammar implements PartialGrammar
{
    /**
     * @param Closure():GrammarPipeline $pipeline
     */
    public function __construct(
        private readonly Closure $pipeline
    ) {}

    public function compile(QueryStructure $structure): iterable
    {
        if ($structure->unions === null) {
            return;
        }

        $query = call_user_func($this->pipeline)->pipe($structure->unions);

        yield new UnionClause();
        yield from $query->getClauses();
    }
}
