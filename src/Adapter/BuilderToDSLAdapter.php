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

namespace PhpGraphGroup\CypherQueryBuilder\Adapter;

use PhpGraphGroup\CypherQueryBuilder\GrammarPipeline;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use WikibaseSolutions\CypherDSL\Query;

final class BuilderToDSLAdapter
{
    public function readOnlyQuery(QueryStructure $structure): Query
    {
        return GrammarPipeline::create()
            ->withMatchGrammar(strict: false)
            ->withCallGrammar()
            ->withWhereGrammar()
            ->withReturnGrammar()
            ->pipe(clone $structure);
    }

    public function setQuery(QueryStructure $structure): Query
    {
        return GrammarPipeline::create()
            ->withMatchGrammar(true)
            ->withCallGrammar()
            ->withWhereGrammar()
            ->withSetGrammar()
            ->pipe(clone $structure);
    }

    public function createQuery(QueryStructure $structure): Query
    {
        return GrammarPipeline::create()
            ->withMatchGrammar(true)
            ->withCallGrammar()
            ->withWhereGrammar()
            ->withCreateGrammar()
            ->pipe(clone $structure);
    }

    public function removeQuery(QueryStructure $structure): Query
    {
        return GrammarPipeline::create()
            ->withMatchGrammar(true)
            ->withCallGrammar()
            ->withWhereGrammar()
            ->withRemoveGrammar()
            ->pipe(clone $structure);
    }

    public function fullPipeline(): GrammarPipeline
    {
        return GrammarPipeline::all();
    }

    public function merge(QueryStructure $structure): Query
    {
        return GrammarPipeline::create()
            ->withMatchGrammar(false)
            ->withWhereGrammar()
            ->withCallGrammar()
            ->withMergeGrammar()
            ->pipe(clone $structure);
    }

    public function delete(QueryStructure $structure): Query
    {
        return GrammarPipeline::create()
            ->withMatchGrammar(false)
            ->withWhereGrammar()
            ->withCallGrammar()
            ->withDeleteGrammar()
            ->pipe(clone $structure);
    }
}
