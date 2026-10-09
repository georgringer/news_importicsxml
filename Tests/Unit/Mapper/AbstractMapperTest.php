<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Mapper;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\NewsImporticsxml\Mapper\AbstractMapper;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AbstractMapperTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[Test]
    public function cleanBeforeImportOnlyRemovesRecordsOfTheImportSource(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('delete')
            ->with(
                'tx_news_domain_model_news',
                ['deleted' => 0, 'pid' => 42, 'import_source' => 'newsimporticsxml_ics']
            );
        $connectionPool = $this->createMock(ConnectionPool::class);
        $connectionPool->method('getConnectionForTable')->with('tx_news_domain_model_news')->willReturn($connection);
        GeneralUtility::addInstance(ConnectionPool::class, $connectionPool);

        $mapper = new class () extends AbstractMapper {
            public function __construct() {}

            public function clean(int $pid, string $importSource): void
            {
                $this->removeImportedRecordsFromPid($pid, $importSource);
            }
        };
        $mapper->clean(42, 'newsimporticsxml_ics');
    }
}
