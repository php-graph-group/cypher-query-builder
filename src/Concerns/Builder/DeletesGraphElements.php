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

use PhpGraphGroup\CypherQueryBuilder\Common\Variable;
use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\DeletingBuilder;

/**
 * @implements DeletingBuilder
 */
trait DeletesGraphElements
{
    use HasQueryStructure;

    public function deleting(string ...$variables): static
    {
        $this->structure->delete = array_values(array_merge(
            $this->getStructure()->delete,
            array_map(static fn ($x) => new Variable($x), $variables)
        ));

        return $this;
    }

    public function forcingDeletion(string ...$variables): static
    {
        $this->structure->forceDelete = true;

        return $this->deleting(...$variables);
    }
}
