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

use RuntimeException;

trait CoalescesTypesAndLabelLists
{
    /**
     * @param string|list<string>|null $labelOrType
     *
     * @return list<string>
     */
    private function coalesce(string|array|null $labelOrType): array
    {
        if ($labelOrType === null) {
            return [];
        }

        if (is_string($labelOrType)) {
            return [$labelOrType];
        }

        return $labelOrType;
    }

    /**
     * @param string|list<string>|null $labelOrType
     *
     * @return non-empty-list<string>
     */
    private function coalesceStrict(string|array|null $labelOrType): array
    {
        $tbr = $this->coalesce($labelOrType);

        if (count($tbr) === 0) {
            throw new RuntimeException('Received empty label or type list');
        }

        return $tbr;
    }
}
