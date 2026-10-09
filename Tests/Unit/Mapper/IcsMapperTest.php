<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Mapper;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\NewsImporticsxml\Mapper\IcsMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class IcsMapperTest extends TestCase
{
    #[Test]
    public function shortUidIsUsedAsImportId(): void
    {
        self::assertSame('abc@example.org', $this->getImportId('abc@example.org'));
    }

    #[Test]
    public function longUidIsHashed(): void
    {
        $uid = str_repeat('a', 90) . '@example.org';

        self::assertSame(md5($uid), $this->getImportId($uid));
    }

    private function getImportId(string $uid): string
    {
        $mapper = (new \ReflectionClass(IcsMapper::class))->newInstanceWithoutConstructor();

        return (new \ReflectionMethod($mapper, 'getImportId'))->invoke($mapper, $uid);
    }
}
