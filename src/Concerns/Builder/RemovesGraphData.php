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
use PhpGraphGroup\CypherQueryBuilder\Concerns\StringDecoder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\RemovingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Set\LabelAssignment;

/**
 * @implements RemovingBuilder
 */
trait RemovesGraphData
{
    use HasQueryStructure;
    use StringDecoder;

    public function removing(string ...$propertiesAndLabels): static
    {
        foreach ($propertiesAndLabels as $propertiesAndLabel) {
            $this->structure->remove[] = $this->decodeToPropertyOrLabel($propertiesAndLabel);
        }

        return $this;
    }

    public function removingLabel(string $name, string ...$labels): static
    {
        if (count($labels)) {
            $this->structure->remove[] = new LabelAssignment(new Variable($name), $labels);
        }

        return $this;
    }
}
