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

use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\CreatingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\DeletingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\MergingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\RemovingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\SettingBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\SubQueryBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\UnionisingBuilder;

interface Builder extends SubQueryBuilder, RemovingBuilder, DeletingBuilder, CreatingBuilder, HasStaticClient, RunnableQueryBuilder, MergingBuilder, SettingBuilder, UnionisingBuilder
{
}
