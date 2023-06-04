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
use WikibaseSolutions\CypherDSL\Clauses\CallClause;

final class CallGrammar implements PartialGrammar
{
    /**
     * @param Closure():GrammarPipeline $subGrammar
     */
    public function __construct(
        private readonly Closure $subGrammar
    ) {}

    public function compile(QueryStructure $structure): iterable
    {
        if (count($structure->subQueries) === 0) {
            return;
        }

        $recursiveGrammar = call_user_func($this->subGrammar);

        foreach ($structure->subQueries as $subQuery) {
            yield (new CallClause())->withSubQuery($recursiveGrammar->pipe($subQuery));
        }
    }
}
