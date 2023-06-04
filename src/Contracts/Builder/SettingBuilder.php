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

namespace PhpGraphGroup\CypherQueryBuilder\Contracts\Builder;

interface SettingBuilder
{
    public function settingMany(array $properties): static;

    public function setting(string $property, mixed $value): static;

    public function settingRaw(string ...$cypher): static;

    /**
     * @param non-empty-list<string>|string $label
     *
     * @return $this
     */
    public function settingLabel(string $name, array|string $label): static;
}
