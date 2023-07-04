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

namespace PhpGraphGroup\CypherQueryBuilder\Common;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<string, mixed>
 */
class ParameterStack implements IteratorAggregate
{
    /** @var array<string, Parameter> */
    private array $parameters = [];

    /**
     * @template Value
     *
     * @param Value $value
     *
     * @return (Value is RawExpression ? RawExpression : Parameter)
     */
    public function add(mixed $value, string $paramNameOverride = null): Parameter|RawExpression
    {
        if ($value instanceof RawExpression) {
            return $value;
        }

        $param = new Parameter($paramNameOverride ?? 'param'.count($this->parameters), $value);

        $this->parameters[$param->name] = $param;

        return $param;
    }

    /**
     * @return array<string, Parameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return ArrayIterator<string, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(array_map(static fn (Parameter $param): mixed => $param->value, $this->parameters));
    }
}
