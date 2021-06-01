<?php
declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Mapper;

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use PicoFeed\Config\Config;
use PicoFeed\Parser\Item;
use PicoFeed\Reader\Reader;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use SimpleXMLElement;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class XmlMapper extends AbstractMapper implements MapperInterface
{
    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var ClientInterface
     */
    protected $client;

    public function __construct()
    {
        parent::__construct();

        $this->requestFactory = GeneralUtility::makeInstance(RequestFactoryInterface::class);
        $this->client = GeneralUtility::makeInstance(ClientInterface::class);
    }

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

        $readerConfig = new Config();
        $readerConfig->setContentFiltering(false);
        $reader = new Reader($readerConfig);
        $resource = $reader->discover($configuration->getPath());

        $parser = $reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding()
        );

        $items = $parser->execute()->getItems();

        foreach ($items as $item) {
            $id = strlen($item->getId()) > 100 ? md5($item->getId()) : $item->getId();
            /** @var Item $item */

            $singleItem = [
                'import_source' => $this->getImportSource(),
                'import_id' => $id,
                'crdate' => $GLOBALS['EXEC_TIME'],
                'cruser_id' => $GLOBALS['BE_USER']->user['uid'],
                'type' => 0,
                'hidden' => 0,
                'pid' => $configuration->getPid(),
                'title' => $item->getTitle(),
                'bodytext' => $this->cleanup($item->getContent()),
                'author' => $item->getAuthor(),
                'media' => $this->getRemoteFile($item->getEnclosureUrl(), $item->getEnclosureType(), $item->getId()),
                'datetime' => $item->getDate()->getTimestamp(),
                'categories' => $this->getCategories($item->xml, $configuration),
                '_dynamicData' => [
                    'reference' => $item,
                    'news_importicsxml' => [
                        'importDate' => date('d.m.Y h:i:s', $GLOBALS['EXEC_TIME']),
                        'feed' => $configuration->getPath(),
                        'url' => $item->getUrl(),
                        'guid' => $item->getTag('guid'),
                    ]
                ],
            ];
            if ($configuration->isPersistAsExternalUrl()) {
                $singleItem['type'] = 2;
                $singleItem['externalurl'] = $item->getUrl();
            }
            if ($configuration->isSetSlug()) {
                $singleItem['path_segment'] = $this->slugHelper->generate($singleItem, $configuration->getPid());
            }

            $data[] = $singleItem;
        }

        return $data;
    }

    protected function getRemoteFile($url, $mimeType, $id)
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
        ];

        $media = [];
        if (!empty($url) && isset($extensions[$mimeType])) {
            $status = false;
            $file = 'uploads/tx_newsimporticsxml/' . $id . '_' . md5($url) . '.' . $extensions[$mimeType];
            if (is_file(Environment::getPublicPath() . '/' . $file)) {
                $status = true;
            } else {
                $imageContent = $this->getImage();
                if ($imageContent !== '') {
                    $status = GeneralUtility::writeFile(Environment::getPublicPath() . '/' . $file, $imageContent);
                }
            }

            if ($status) {
                $media[] = [
                    'image' => $file,
                    'showinpreview' => true
                ];
            }
        }
        return $media;
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

    /**
     * @param string $content
     * @return string
     */
    protected function cleanup($content): string
    {
        $search = ['<br />', '<br>', '<br/>', LF . LF];
        $replace = [LF, LF, LF, LF];
        return str_replace($search, $replace, $content);
    }

    /**
     * @return string
     */
    public function getImportSource(): string
    {
        return 'newsimporticsxml_xml';
    }

    private function getImage(string $url): string
    {
        $allowedRetries = 5;
        $currentRetries = 0;
        $imageContent = '';

        do {
            $response = $this->client->sendRequest($this->requestFactory->createRequest('GET', $url));
            $imageContent = (string) $response->getBody();

            $expectedSize = intval($response->getHeader('Content-Length')[0] ?? 0);
            $actualSize = strlen((string) $response->getBody());
        } while($currentRetries++ < $allowedRetries && $expectedSize !== $actualSize);

        return $imageContent;
    }
}
