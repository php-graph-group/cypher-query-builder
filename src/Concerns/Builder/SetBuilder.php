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

use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Common\Variable;
use PhpGraphGroup\CypherQueryBuilder\Concerns\HasQueryStructure;
use PhpGraphGroup\CypherQueryBuilder\Concerns\StringDecoder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\SettingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Set\LabelAssignment;
use PhpGraphGroup\CypherQueryBuilder\Set\PropertyAssignment;

/**
 * @implements SettingBuilder
 */
trait SetBuilder
{
    use HasQueryStructure;
    use StringDecoder;

    public function settingMany(array $properties): static
    {
        foreach ($properties as $key => $value) {
            if (is_int($key)) {
                $label = $this->stringToLabel($value);

                $this->structure->set[] = $label;
            } else {
                $this->setting($key, $value);
            }
        }

        return $this;
    }

    public function setting(string $property, mixed $value): static
    {
        $property = $this->stringToProperty($property);
        $value = $this->structure->parameters->add($value);

        $this->structure->set[] = new PropertyAssignment($property, $value);

        return $this;
    }

    public function settingRaw(string ...$cypher): static
    {
        foreach ($cypher as $raw) {
            $this->structure->set[] = new RawExpression($raw);
        }

        return $this;
    }

    public function settingLabel(string $name, array|string $label): static
    {
        if (is_string($label)) {
            $label = [$label];
        }

        $this->structure->set[] = new LabelAssignment(new Variable($name), $label);

        return $this;
    }
}
