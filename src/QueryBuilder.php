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

use PhpGraphGroup\CypherQueryBuilder\Adapter\BuilderToDSLAdapter;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\CreatesGraphElements;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\DeletesGraphElements;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\FiltersGraphTraversal;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\MatchesGraphs;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\MergesGraphElements;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\RemovesGraphData;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\ReturnsGraphData;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\SetBuilder;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\UnionisesQueries;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder as IBuilder;

class QueryBuilder implements IBuilder
{
    use Builder;
    use CreatesGraphElements;
    use DeletesGraphElements;
    use MatchesGraphs;
    use MergesGraphElements;
    use RemovesGraphData;
    use ReturnsGraphData;
    use SetBuilder;
    use UnionisesQueries;
    use FiltersGraphTraversal;
    use Builder\CallsSubQueries;
    use Builder\HasStaticClient;
    use Builder\RunsQueries;

    private function __construct(
        private readonly QueryStructure $structure,
        private readonly BuilderToDSLAdapter $adapter
    ) {}
}
