<?php

namespace GeorgRinger\NewsImporticsxml\Tasks;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GeorgRinger\NewsImporticsxml\Jobs\ImportJob;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Provides Import task
 */
class ImportTask extends AbstractTask
{

    /** @var string */
    public $email;

    /** @var string */
    public $path;

    /** @var string */
    public $mapping;

    /** @var string */
    public $format;

    /** @var int */
    public $pid;

    /** @var int */
    public $persistAsExternalUrl;

    public function execute()
    {
        $success = true;
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $importJob = $objectManager->get(ImportJob::class, $this->createConfiguration());
        $importJob->run();

        return true;
    }

    /**
     * This method returns additional information about the specific task
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        return sprintf('%s: %s,' . LF . ' %s: %s ' . LF . '%s: %s',
            $this->translate('format'), strtoupper($this->format),
            $this->translate('path'), GeneralUtility::fixed_lgd_cs($this->path, 200),
            $this->translate('pid'), $this->pid);
    }

    /**
     * @return TaskConfiguration
     */
    protected function createConfiguration(): TaskConfiguration
    {
        $configuration = new TaskConfiguration();
        $configuration->setEmail($this->email);
        $configuration->setPath($this->path);
        $configuration->setMapping($this->mapping);
        $configuration->setFormat($this->format);
        $configuration->setPid($this->pid);
        $configuration->setPersistAsExternalUrl($this->persistAsExternalUrl);

        return $configuration;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function translate(string $key): string
    {
        /** @var LanguageService $languageService */
        $languageService = $GLOBALS['LANG'];
        return $languageService->sL('LLL:EXT:news_importicsxml/Resources/Private/Language/locallang.xlf:' . $key);
    }
}
