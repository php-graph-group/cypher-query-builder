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

namespace PhpGraphGroup\CypherQueryBuilder;

use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\CallGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\CreateGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\DeleteGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\MatchGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\MergeGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\RemoveGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\ReturnGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\SetGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\UnionGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\WhereGrammar;
use PhpGraphGroup\CypherQueryBuilder\Adapter\Partial\WithAllGrammar;
use PhpGraphGroup\CypherQueryBuilder\Contracts\PartialGrammar;
use WikibaseSolutions\CypherDSL\Query;

class GrammarPipeline
{
    /**
     * @param list<Contracts\PartialGrammar> $grammars
     */
    private function __construct(
        private readonly array $grammars
    ) {}

    public static function create(): self
    {
        return new self([]);
    }

    public function withCreateGrammar(): self
    {
        return $this->withPartialGrammar(new CreateGrammar());
    }

    public function withDeleteGrammar(): self
    {
        return $this->withPartialGrammar(new DeleteGrammar());
    }

    public function withMatchGrammar(bool $strict): self
    {
        return $this->withPartialGrammar(new MatchGrammar($strict));
    }

    public function withReturnGrammar(): self
    {
        return $this->withPartialGrammar(new ReturnGrammar());
    }

    public function withSetGrammar(): self
    {
        return $this->withPartialGrammar(new SetGrammar());
    }

    public function withWhereGrammar(): self
    {
        return $this->withPartialGrammar(new WhereGrammar(static function () {
            return GrammarPipeline::create()
                ->withMatchGrammar(false)
                ->withCallGrammar()
                ->withWhereGrammar()
                ->withReturnGrammar();
        }));
    }

    public function withPartialGrammar(PartialGrammar $grammar): self
    {
        return new self(array_merge($this->grammars, [$grammar]));
    }

    public function pipe(QueryStructure $structure): Query
    {
        $query = Query::new();
        foreach ($this->grammars as $grammar) {
            foreach ($grammar->compile($structure) as $clause) {
                $query->addClause($clause);
            }
        }

        return $query;
    }

    public function withMergeGrammar(): self
    {
        return $this->withPartialGrammar(new MergeGrammar());
    }

    public function withCallGrammar(): self
    {
        return $this->withPartialGrammar(new CallGrammar(static function () {
            return GrammarPipeline::create()
                ->withPartialGrammar(new WithAllGrammar())
                ->withMatchGrammar(false)
                ->withCallGrammar()
                ->withWhereGrammar()
                ->withReturnGrammar();
        }));
    }

    public function withUnionGrammar(): self
    {
        return $this->withPartialGrammar(new UnionGrammar(static function () {
            return GrammarPipeline::all();
        }));
    }

    public function withRemoveGrammar(): self
    {
        return $this->withPartialGrammar(new RemoveGrammar());
    }

    public static function all(): GrammarPipeline
    {
        return GrammarPipeline::create()
            ->withMatchGrammar(false)
            ->withCallGrammar()
            ->withWhereGrammar()
            ->withMergeGrammar()
            ->withCreateGrammar()
            ->withSetGrammar()
            ->withDeleteGrammar()
            ->withReturnGrammar()
            ->withUnionGrammar();
    }
}
