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

namespace PhpGraphGroup\CypherQueryBuilder\Concerns;

use PhpGraphGroup\CypherQueryBuilder\Common\Alias;
use PhpGraphGroup\CypherQueryBuilder\Common\Property;
use PhpGraphGroup\CypherQueryBuilder\Common\Variable;
use PhpGraphGroup\CypherQueryBuilder\Set\LabelAssignment;

trait StringDecoder
{
    use HasQueryStructure;

    /**
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    private function stringToProperty(string $property): Property
    {
        if (str_contains($property, '.')) {
            [ $variable, $property ] = explode('.', $property, 2);
            $variable = new Variable($variable);
        } else {
            $variable = $this->structure->entry;
        }

        return new Property($variable, $property);
    }

    /**
     * @return Property|Alias<Property>
     *
     * @psalm-suppress InvalidArrayOffset
     */
    private function stringToAliasableProperty(string $property): Property|Alias
    {
        $origin = $property;
        $requiresAlias = false;
        if (!str_contains($property, '.')) {
            $requiresAlias = true;
        }
        $property = $this->stringToProperty($property);

        if (preg_match('/^(?<property>[\w\W]+?) as (?<alias>[\w\W]+)$/i', $property->name, $matches)) {
            $property = new Property($property->variable, $matches[0]['property']);

            return new Alias($property, $matches[0]['alias']);
        }

        if ($requiresAlias) {
            return new Alias($property, $origin);
        }

        return $property;
    }

    private function stringToLabel(string $label): LabelAssignment
    {
        $variable = '';
        if (str_contains($label, ':')) {
            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [ $variable, $label ] = explode(':', $label, 2);
        }

        if ($variable === '') {
            $variable = $this->structure->entry;
        } else {
            $variable = new Variable($variable);
        }

        $labels = explode(':', $label);

        return new LabelAssignment($variable, $labels);
    }

    private function decodeToPropertyOrLabel(string $media): LabelAssignment|Property
    {
        if (str_contains($media, ':')) {
            return $this->stringToLabel($media);
        }

        return $this->stringToProperty($media);
    }
}
