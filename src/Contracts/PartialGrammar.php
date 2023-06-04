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

namespace PhpGraphGroup\CypherQueryBuilder\Contracts;

use PhpGraphGroup\CypherQueryBuilder\QueryStructure;
use WikibaseSolutions\CypherDSL\Clauses\Clause;

/**
 * Compiles a part of the query structure into a Cypher clauses.
 */
interface PartialGrammar
{
    /**
     * @return iterable<Clause>
     */
    public function compile(QueryStructure $structure): iterable;
}
