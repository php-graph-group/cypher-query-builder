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

namespace PhpGraphGroup\CypherQueryBuilder\Concerns\Builder;

use PhpGraphGroup\CypherQueryBuilder\Builders\SubQueryBuilder;
use PhpGraphGroup\CypherQueryBuilder\Common\GraphPattern;
use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\CallingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\SubQueryBuilder as SubQueryBuilderContract;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;

/**
 * @implements CallingBuilder
 */
trait CallsSubQueries
{
    use HasQueryStructure;

    private function createSubQueryBuilder(): SubQueryBuilder
    {
        return new SubQueryBuilder(new QueryStructure(
            $this->structure->parameters,
            new GraphPattern(),
            $this->structure->entry
        ));
    }

    /**
     * @return $this
     */
    public function calling(callable|SubQueryBuilderContract $callable): static
    {
        if (is_callable($callable)) {
            $builder = $this->createSubQueryBuilder();

            $callable($builder);

            $callable = $builder;
        }

        $this->structure->subQueries[] = $callable->getStructure();

        return $this;
    }
}
