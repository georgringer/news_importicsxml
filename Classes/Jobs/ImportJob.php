<?php

namespace GeorgRinger\NewsImporticsxml\Jobs;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

/**
 * Base import handling
 */
class ImportJob
{

    /**
     * @var TaskConfiguration
     */
    protected $configuration;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \GeorgRinger\NewsImporticsxml\Mapper\XmlMapper
     * @inject
     */
    protected $xmlMapper;

    /**
     * @var \GeorgRinger\NewsImporticsxml\Mapper\IcsMapper
     * @inject
     */
    protected $icsMapper;

    /**
     * @var \GeorgRinger\News\Domain\Service\NewsImportService
     * @inject
     */
    protected $newsImportService;

    /**
     * @param TaskConfiguration $configuration
     */
    public function __construct(TaskConfiguration $configuration)
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->configuration = $configuration;
    }

    /**
     * Import remote content
     */
    public function run()
    {
        $this->logger->info(sprintf(
            'Starting import of "%s" (%s), reporting to "%s"',
            $this->configuration->getPath(),
            strtoupper($this->configuration->getFormat()),
            $this->configuration->getEmail()));

        switch (strtolower($this->configuration->getFormat())) {
            case 'xml':
                $data = $this->xmlMapper->map($this->configuration);
                break;
            case 'ics':
                $data = $this->icsMapper->map($this->configuration);
                break;
            default:
                $message = sprintf('Format "%s" is not supported!', $this->configuration->getFormat());
                $this->logger->critical($message);
                throw new UnexpectedValueException($message, 1527601575);
        }

        $this->import($data);
    }

    /**
     * @param array $data
     */
    protected function import(array $data = null)
    {
        $this->logger->info(sprintf('Starting import of %s records', count($data)));
        $this->newsImportService->import($data);
    }
}
