<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Mapper;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractMapper
{
    protected LoggerInterface $logger;
    protected SlugHelper $slugHelper;

    protected array $extensionConfiguration = [];

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $fieldConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'] ?? [];
        $this->slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $fieldConfig);

        try {
            $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('news_importicsxml');
        } catch (\Exception $e) {
            // do nothing
        }
    }

    protected function removeImportedRecordsFromPid(int $pid, string $importSource): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_news_domain_model_news');
        $connection->delete(
            'tx_news_domain_model_news',
            [
                'deleted' => 0,
                'pid' => $pid,
                'import_source' => $importSource,
            ]
        );
    }
}
