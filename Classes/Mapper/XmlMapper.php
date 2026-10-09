<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Mapper;

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GuzzleHttp\Exception\TransferException;
use PicoFeed\Config\Config;
use PicoFeed\Parser\Item;
use PicoFeed\Reader\Reader;
use PicoFeed\Reader\SubscriptionNotFoundException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SimpleXMLElement;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class XmlMapper extends AbstractMapper implements MapperInterface
{
    public function map(TaskConfiguration $configuration): array
    {
        if ($configuration->getCleanBeforeImport()) {
            $this->removeImportedRecordsFromPid($configuration->getPid(), $this->getImportSource());
        }

        $data = [];

        $readerConfig = new Config();
        $readerConfig->setContentFiltering(false);
        $reader = new Reader($readerConfig);
        [$url, $content, $encoding] = $this->loadFeed($reader, $configuration->getPath());

        $parser = $reader->getParser($url, $content, $encoding);

        $items = $parser->execute()->getItems();

        foreach ($items as $item) {
            $id = strlen($item->getId()) > 100 ? md5($item->getId()) : $item->getId();
            /** @var Item $item */
            $singleItem = [
                'import_source' => $this->getImportSource(),
                'import_id' => $id,
                'crdate' => $GLOBALS['EXEC_TIME'],
                'cruser_id' => isset($GLOBALS['BE_USER'], $GLOBALS['BE_USER']->user) ? $GLOBALS['BE_USER']->user['uid'] : 0,
                'type' => 0,
                'hidden' => 0,
                'pid' => $configuration->getPid(),
                'title' => $item->getTitle(),
                'teaser' => trim((string)$item->xml->description),
                'bodytext' => trim($this->cleanup($item->getContent())),
                'author' => $item->getAuthor(),
                'datetime' => $item->getDate()->getTimestamp(),
                'categories' => $this->getCategories($item->xml, $configuration),
                '_dynamicData' => [
                    'reference' => $item,
                    'news_importicsxml' => [
                        'importDate' => date('d.m.Y h:i:s', $GLOBALS['EXEC_TIME']),
                        'feed' => $configuration->getPath(),
                        'url' => $item->getUrl(),
                        'guid' => $item->getTag('guid'),
                    ],
                ],
            ];
            $this->addRemoteFiles($singleItem, $item->xml, $configuration->getPath());
            if ($configuration->isPersistAsExternalUrl()) {
                $singleItem['type'] = 2;
                $singleItem['externalurl'] = $item->getUrl();
            }
            if ($configuration->isSetSlug()) {
                $singleItem['generate_path_segment'] = true;
            }

            $data[] = $singleItem;
        }
        return $data;
    }

    protected function addRemoteFiles(array &$singleItem, \SimpleXMLElement $xml, string $xmlPath)
    {
        $extensions = [
            'image/jpg' => 'jpg',
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
        ];

        $targetPath = $this->getTargetPath();
        if ($targetPath === null) {
            $this->logger->warning('Import path must be a folder inside the public directory, enclosures are skipped', ['importPath' => $this->extensionConfiguration['importPath'] ?? '']);
            return;
        }
        // The URL path never contributes a directory, all files of a feed end up in one folder
        $path = $targetPath . substr(md5($xmlPath), 0, 10) . '/';

        foreach ($xml->enclosure as $enclosure) {
            $url = (string)$enclosure->attributes()['url'];
            $mimeType = strtolower(trim((string)$enclosure->attributes()['type']));
            if (empty($url) || !isset($extensions[$mimeType])) {
                continue;
            }
            $url = $this->normalizeRemoteUrl($url);
            if ($url === null) {
                $this->logger->warning('Enclosure skipped, only http and https URLs are allowed', ['url' => (string)$enclosure->attributes()['url']]);
                continue;
            }
            $extension = $extensions[$mimeType];
            $file = $path . $this->getFileName($url, $extension);
            $absoluteFile = $this->getPublicPath() . $file;

            if (!$this->fileExists($absoluteFile)) {
                $content = $this->fetchUrl($url);
                if (!is_string($content) || $content === '') {
                    $this->logger->warning('Enclosure skipped, download failed or empty', ['url' => $url]);
                    continue;
                }
                if ($this->detectExtension($content) !== $extension) {
                    $this->logger->warning('Enclosure skipped, content does not match the declared type', ['url' => $url, 'type' => $mimeType]);
                    continue;
                }
                if (!$this->writeFile($absoluteFile, $content)) {
                    $this->logger->warning('Enclosure skipped, file could not be written', ['url' => $url, 'file' => $file]);
                    continue;
                }
            }

            if ($extension === 'pdf') {
                $singleItem['related_files'][] = [
                    'file' => $file,
                ];
            } else {
                $singleItem['media'][] = [
                    'image' => $file,
                    'showinpreview' => true,
                ];
            }
        }
    }

    /**
     * Configured import folder relative to the public path, with leading and trailing slash.
     * Returns null if the folder would leave the public directory.
     */
    protected function getTargetPath(): ?string
    {
        $targetPath = trim((string)($this->extensionConfiguration['importPath'] ?? ''), '/');
        $targetPath = $targetPath ?: 'uploads/tx_newsimporticsxml';
        foreach (explode('/', str_replace('\\', '/', $targetPath)) as $segment) {
            if ($segment === '..' || strpos($segment, "\0") !== false) {
                return null;
            }
        }
        return '/' . $targetPath . '/';
    }

    /**
     * Returns the URL with a lower-case scheme, or null if it is not an http(s) URL with a host.
     * GeneralUtility::getUrl() only uses the HTTP client for a lower-case scheme and reads
     * local files otherwise.
     */
    protected function normalizeRemoteUrl(string $url): ?string
    {
        $urlInfo = parse_url(trim($url));
        if (!is_array($urlInfo) || empty($urlInfo['scheme']) || empty($urlInfo['host'])) {
            return null;
        }
        $scheme = strtolower($urlInfo['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }
        $url = trim($url);
        return $scheme . substr($url, strlen($urlInfo['scheme']));
    }

    /**
     * Sanitized last path segment of the URL plus a hash of the full URL. The extension is
     * the one of the declared type and never taken from the URL.
     */
    protected function getFileName(string $url, string $extension): string
    {
        $urlPath = (string)(parse_url($url, PHP_URL_PATH) ?? '');
        $baseName = rawurldecode(basename(str_replace('\\', '/', $urlPath)));
        $dotPosition = strrpos($baseName, '.');
        if ($dotPosition !== false) {
            $baseName = substr($baseName, 0, $dotPosition);
        }
        $baseName = substr(trim((string)preg_replace('/[^A-Za-z0-9_-]+/', '_', $baseName), '_-'), 0, 60);
        if ($baseName === '') {
            $baseName = 'file';
        }
        return $baseName . '_' . substr(md5($url), 0, 10) . '.' . $extension;
    }

    /**
     * Extension derived from the downloaded bytes, or null if the type is not allowed
     */
    protected function detectExtension(string $content): ?string
    {
        if (strncmp($content, "\xFF\xD8\xFF", 3) === 0) {
            return 'jpg';
        }
        if (strncmp($content, "\x89PNG\r\n\x1A\n", 8) === 0) {
            return 'png';
        }
        if (strncmp($content, 'GIF87a', 6) === 0 || strncmp($content, 'GIF89a', 6) === 0) {
            return 'gif';
        }
        if (strncmp($content, '%PDF-', 5) === 0) {
            return 'pdf';
        }
        return null;
    }

    protected function getPublicPath(): string
    {
        return Environment::getPublicPath();
    }

    protected function fileExists(string $absoluteFile): bool
    {
        return is_file($absoluteFile);
    }

    /**
     * Downloads the feed with the HTTP client of TYPO3 and follows the feed link if the url points to a HTML page
     *
     * @return array{0: string, 1: string, 2: string} url, content and encoding of the feed
     */
    protected function loadFeed(Reader $reader, string $path): array
    {
        $url = $reader->prependScheme($path);
        $response = $this->request($url);
        if ($response === null) {
            throw new RuntimeException(sprintf('The feed "%s" could not be downloaded', $url), 1760026001);
        }
        $content = (string)$response->getBody();

        if (!$reader->detectFormat($content)) {
            $links = $reader->find($url, $content);
            if (empty($links)) {
                throw new SubscriptionNotFoundException('Unable to find a subscription');
            }
            $url = $links[0];
            $response = $this->request($url);
            if ($response === null) {
                throw new RuntimeException(sprintf('The feed "%s" could not be downloaded', $url), 1760026002);
            }
            $content = (string)$response->getBody();
        }

        $encoding = preg_match('/charset=["\']?([\w-]+)/i', $response->getHeaderLine('Content-Type'), $matches) ? $matches[1] : '';

        return [$url, $content, $encoding];
    }

    /**
     * @return string|false
     */
    protected function fetchUrl(string $url)
    {
        $response = $this->request($url);

        return $response === null ? false : (string)$response->getBody();
    }

    protected function request(string $url): ?ResponseInterface
    {
        try {
            return GeneralUtility::makeInstance(RequestFactory::class)->request($url);
        } catch (TransferException $e) {
            return null;
        }
    }

    protected function writeFile(string $absoluteFile, string $content): bool
    {
        GeneralUtility::mkdir_deep(dirname($absoluteFile));
        return GeneralUtility::writeFile($absoluteFile, $content);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param TaskConfiguration $configuration
     * @return array
     */
    protected function getCategories(SimpleXMLElement $xml, TaskConfiguration $configuration)
    {
        $categoryIds = $categoryTitles = [];
        $categories = $xml->category;
        if ($categories) {
            foreach ($categories as $cat) {
                $categoryTitles[] = (string)$cat;
            }
        }
        if (!empty($categoryTitles)) {
            if (!$configuration->getMapping()) {
                $this->logger->info('Categories found during import but no mapping assigned in the task!');
            } else {
                $categoryMapping = $configuration->getMappingConfigured();
                foreach ($categoryTitles as $title) {
                    if (!isset($categoryMapping[$title])) {
                        $this->logger->warning(sprintf('Category mapping is missing for category "%s"', $title));
                    } else {
                        $categoryIds[] = $categoryMapping[$title];
                    }
                }
            }
        }

        return $categoryIds;
    }

    protected function cleanup(string $content): string
    {
        $search = ['<br />', '<br>', '<br/>', LF . LF];
        $replace = [LF, LF, LF, LF];
        return str_replace($search, $replace, $content);
    }

    public function getImportSource(): string
    {
        return 'newsimporticsxml_xml';
    }
}
