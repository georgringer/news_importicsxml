<?php

namespace GeorgRinger\NewsImporticsxml\Mapper;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractMapper
{

    /** @var $logger Logger */
    protected $logger;

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    protected function removeImportedRecordsFromPid(int $pid, string $importSource)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_news_domain_model_news');
        $connection->delete(
            'tx_news_domain_model_news',
            [
                'deleted' => 0,
                'pid' => $pid,
                'import_source' => $importSource
            ]
        );
    }
}
