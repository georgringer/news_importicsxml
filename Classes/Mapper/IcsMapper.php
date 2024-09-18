<?php
declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Mapper;

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use ICal;
use RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class IcsMapper extends AbstractMapper implements MapperInterface
{

    /** @var bool */
    protected $pathIsModified = false;

    /**
     * @param TaskConfiguration $configuration
     * @return array
     */
    public function map(TaskConfiguration $configuration)
    {
        if ($configuration->getCleanBeforeImport()) {
            $this->removeImportedRecordsFromPid($configuration->getPid(), $this->getImportSource());
        }

        $data = [];
        $path = $this->getFileContent($configuration);

        $idCount = [];

        require_once(ExtensionManagementUtility::extPath('news_importicsxml') . 'Resources/Private/Contrib/Ical.php');
        $iCalService = new ICal($path);
        $events = $iCalService->events();

        foreach ($events as $event) {
            $id = strlen($event['UID']) < 90 ? $event['UID'] : md5($event['UID']);
            if (!isset($idCount[$id])) {
                $idCount[$id] = 1;
            } else {
                $idCount[$id]++;
            }
            $datetime = $iCalService->iCalDateToUnixTimestamp($event['DTSTART'] ?? $event['DTSTAMP']);
            if ($datetime === false) {
                $datetime = $iCalService->iCalDateToUnixTimestamp($event['DTSTAMP']);
            }

            $singleItem = [
                'import_source' => $this->getImportSource(),
                'import_id' => $id . '-' . $idCount[$id],
                'crdate' => $GLOBALS['EXEC_TIME'],
                'cruser_id' => $GLOBALS['BE_USER'] ?? $GLOBALS['BE_USER']->user['uid'] ?? 0,
                'type' => 0,
                'hidden' => 0,
                'pid' => $configuration->getPid(),
                'title' => $this->cleanup((string)$event['SUMMARY']),
                'bodytext' => $this->cleanup((string)$event['DESCRIPTION']),
                'datetime' => $datetime,
                'categories' => $this->getCategories((array)($event['CATEGORIES_array'] ?? []), $configuration),
                '_dynamicData' => [
                    'location' => (isset($event['LOCATION']) ? $event['LOCATION'] : ''),
                    'datetime_end' => (isset($event['DTEND']) ? $iCalService->iCalDateToUnixTimestamp($event['DTEND']) : ''),
                    'reference' => $event,
                    'news_importicsxml' => [
                        'importDate' => date('d.m.Y h:i:s', $GLOBALS['EXEC_TIME']),
                        'feed' => $configuration->getPath(),
                        'UID' => $event['UID'],
                        'VARIANT' => $idCount[$id],
                        'LOCATION' => (isset($event['LOCATION']) ? $event['LOCATION'] : ''),
                        'DTSTART' => $event['DTSTART'] ?? '',
                        'DTSTAMP' => $event['DTSTAMP'] ?? '',
                        'DTEND' => $event['DTEND'] ?? '',
                        'PRIORITY' => $event['PRIORITY'] ?? '',
                        'SEQUENCE' => $event['SEQUENCE'],
                        'STATUS' => $event['STATUS'] ?? '',
                        'TRANSP' => $event['TRANSP'] ?? '',
                        'URL' => $event['URL'] ?? '',
                        'ATTACH' => $event['ATTACH'] ?? '',
                        'SUMMARY' => $event['SUMMARY'] ?? '',
                    ]
                ],
            ];

            if ($configuration->isSetSlug()) {
                $singleItem['generate_path_segment'] = true;
            }
            $data[] = $singleItem;
        }

        if ($this->pathIsModified) {
            $success = unlink($path);
        }

        return $data;
    }

    /**
     * @param array $categoryTitles
     * @param TaskConfiguration $configuration
     * @return array
     */
    protected function getCategories(array $categoryTitles, TaskConfiguration $configuration): array
    {
        $categoryIds = [];
        if (!empty($categoryTitles)) {
            if (!$configuration->getMapping()) {
                $this->logger->info('Categories found during import but no mapping assigned in the task!');
            } else {
                $categoryMapping = $configuration->getMappingConfigured();

                foreach ($categoryTitles as $rawTitle) {
                    $splitTitle = GeneralUtility::trimExplode(',', $rawTitle, true, 0);

                    foreach ($splitTitle as $title) {
                        if (!isset($categoryMapping[$title])) {
                            $this->logger->warning(sprintf('Category mapping is missing for category "%s"', $title));
                        } else {
                            $categoryIds[] = $categoryMapping[$title];
                        }
                    }
                }
            }
        }

        return $categoryIds;
    }

    /**
     * @param string $content
     * @return string
     */
    protected function cleanup(string $content): string
    {
        $search = ['\\,', '\\n'];
        $replace = [',', chr(10)];

        return str_replace($search, $replace, $content);
    }

    /**
     * @param TaskConfiguration $configuration
     * @return string
     */
    protected function getFileContent(TaskConfiguration $configuration)
    {
        $path = $configuration->getPath();
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $content = $this->getContentOfFile($path);

            $temporaryCopyPath = Environment::getPublicPath() . '/typo3temp/' . md5($path . $GLOBALS['EXEC_TIME']);
            GeneralUtility::writeFileToTypo3tempDir($temporaryCopyPath, $content);
            $this->pathIsModified = true;
        } else {
            $temporaryCopyPath = Environment::getPublicPath() . '/' . $configuration->getPath();
        }

        if (!is_file($temporaryCopyPath)) {
            throw new RuntimeException(sprintf('The path "%s" does not contain a valid file', $temporaryCopyPath));
        }

        return $temporaryCopyPath;
    }

    protected function getContentOfFile($url)
    {
        $response = GeneralUtility::getUrl($url);

        if (empty($response)) {
            $message = sprintf('URL "%s" returned an empty content!', $url);
            $this->logger->alert($message);
            throw new RuntimeException($message);
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getImportSource(): string
    {
        return 'newsimporticsxml_ics';
    }
}
