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

namespace PhpGraphGroup\CypherQueryBuilder\Builders;

use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\CallsSubQueries;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\FiltersGraphTraversal;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\HasStaticClient;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\MatchesGraphs;
use PhpGraphGroup\CypherQueryBuilder\Concerns\Builder\ReturnsGraphData;
use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\QueryStructure;

/**
 * @internal
 */
final class SubQueryBuilder implements \PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\SubQueryBuilder
{
    use MatchesGraphs;
    use CallsSubQueries;
    use FiltersGraphTraversal;
    use ReturnsGraphData;
    use HasStaticClient;
    use HasQueryStructure;

    /**
     * @internal
     */
    public function __construct(
        private readonly QueryStructure $structure
    ) {}
}
