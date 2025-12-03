<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Mapper;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;

interface MapperInterface
{
    public function map(TaskConfiguration $configuration): array;

    public function getImportSource(): string;
}
