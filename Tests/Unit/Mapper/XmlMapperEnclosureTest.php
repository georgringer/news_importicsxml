<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Mapper;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\NewsImporticsxml\Mapper\XmlMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class XmlMapperEnclosureTest extends TestCase
{
    private const PNG = "\x89PNG\r\n\x1A\n" . 'png-body';
    private const PDF = '%PDF-1.4 pdf-body';

    #[Test]
    public function imageIsStoredWithSanitizedNameInFeedFolder(): void
    {
        $mapper = $this->getMapper(['https://example.org/img/My%20Photo!.png' => self::PNG]);
        $item = $this->import($mapper, [['https://example.org/img/My%20Photo!.png', 'image/png']]);

        $expected = '/uploads/tx_newsimporticsxml/' . $this->feedHash() . '/My_Photo_' . substr(md5('https://example.org/img/My%20Photo!.png'), 0, 10) . '.png';
        self::assertSame([['image' => $expected, 'showinpreview' => true]], $item['media']);
        self::assertSame(['/public' . $expected => self::PNG], $mapper->written);
    }

    #[Test]
    public function pdfIsStoredAsRelatedFile(): void
    {
        $mapper = $this->getMapper(['https://example.org/doc.pdf' => self::PDF]);
        $item = $this->import($mapper, [['https://example.org/doc.pdf', 'application/pdf']]);

        self::assertArrayNotHasKey('media', $item);
        self::assertCount(1, $item['related_files']);
        self::assertStringEndsWith('.pdf', $item['related_files'][0]['file']);
    }

    #[Test]
    public function urlPathCannotChooseDirectoryOrExtension(): void
    {
        $url = 'http://feed.test/a/../../../../typo3temp/assets/probe.php';
        $mapper = $this->getMapper([$url => self::PNG]);
        $item = $this->import($mapper, [[$url, 'image/png']]);

        $file = $item['media'][0]['image'];
        self::assertSame('/uploads/tx_newsimporticsxml/' . $this->feedHash() . '/probe_' . substr(md5($url), 0, 10) . '.png', $file);
        self::assertStringNotContainsString('..', $file);
        self::assertStringNotContainsString('.php', $file);
    }

    #[Test]
    public function encodedTraversalInFileNameIsRemoved(): void
    {
        $url = 'https://example.org/x/..%2F..%2Fshell.php%00.png';
        $mapper = $this->getMapper([$url => self::PNG]);
        $item = $this->import($mapper, [[$url, 'image/png']]);

        $fileName = basename($item['media'][0]['image']);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]+_[0-9a-f]{10}\.png$/', $fileName);
        self::assertSame('/uploads/tx_newsimporticsxml/' . $this->feedHash() . '/' . $fileName, $item['media'][0]['image']);
    }

    public static function nonHttpUrlDataProvider(): array
    {
        return [
            'file scheme' => ['file:///etc/passwd'],
            'plain path' => ['/etc/passwd'],
            'php filter' => ['php://filter/resource=/etc/passwd'],
            'ftp' => ['ftp://example.org/a.png'],
            'phar' => ['phar:///tmp/a.phar/a.png'],
            'no host' => ['http:///etc/passwd'],
        ];
    }

    #[Test]
    #[DataProvider('nonHttpUrlDataProvider')]
    public function onlyHttpAndHttpsUrlsAreFetched(string $url): void
    {
        $mapper = $this->getMapper([$url => self::PNG]);
        $item = $this->import($mapper, [[$url, 'image/png']]);

        self::assertArrayNotHasKey('media', $item);
        self::assertSame([], $mapper->fetched);
        self::assertSame([], $mapper->written);
    }

    #[Test]
    public function upperCaseSchemeIsFetchedWithLowerCaseScheme(): void
    {
        $mapper = $this->getMapper(['https://example.org/a.png' => self::PNG]);
        $item = $this->import($mapper, [['HTTPS://example.org/a.png', 'image/png']]);

        self::assertSame(['https://example.org/a.png'], $mapper->fetched);
        self::assertCount(1, $item['media']);
    }

    #[Test]
    public function contentNotMatchingDeclaredTypeIsSkipped(): void
    {
        $mapper = $this->getMapper([
            'https://example.org/a.png' => '<?php echo 1;',
            'https://example.org/b.png' => self::PDF,
        ]);
        $item = $this->import($mapper, [
            ['https://example.org/a.png', 'image/png'],
            ['https://example.org/b.png', 'image/png'],
        ]);

        self::assertArrayNotHasKey('media', $item);
        self::assertSame([], $mapper->written);
    }

    #[Test]
    public function failedDownloadIsSkipped(): void
    {
        $mapper = $this->getMapper([]);
        $item = $this->import($mapper, [['https://example.org/a.png', 'image/png']]);

        self::assertArrayNotHasKey('media', $item);
        self::assertSame([], $mapper->written);
    }

    #[Test]
    public function existingFileIsNotDownloadedAgain(): void
    {
        $url = 'https://example.org/a.png';
        $mapper = $this->getMapper([$url => self::PNG]);
        $mapper->existing['/public/uploads/tx_newsimporticsxml/' . $this->feedHash() . '/a_' . substr(md5($url), 0, 10) . '.png'] = true;
        $item = $this->import($mapper, [[$url, 'image/png']]);

        self::assertSame([], $mapper->fetched);
        self::assertCount(1, $item['media']);
    }

    #[Test]
    public function importPathOutsidePublicDirectoryIsRejected(): void
    {
        $mapper = $this->getMapper(['https://example.org/a.png' => self::PNG], '/fileadmin/../../config/');
        $item = $this->import($mapper, [['https://example.org/a.png', 'image/png']]);

        self::assertArrayNotHasKey('media', $item);
        self::assertSame([], $mapper->fetched);
    }

    #[Test]
    public function configuredImportPathIsUsed(): void
    {
        $mapper = $this->getMapper(['https://example.org/a.png' => self::PNG], 'fileadmin/import');
        $item = $this->import($mapper, [['https://example.org/a.png', 'image/png']]);

        self::assertStringStartsWith('/fileadmin/import/' . $this->feedHash() . '/', $item['media'][0]['image']);
    }

    private function feedHash(): string
    {
        return substr(md5('https://feed.test/feed.xml'), 0, 10);
    }

    private function import(XmlMapper $mapper, array $enclosures): array
    {
        $xml = '<item>';
        foreach ($enclosures as [$url, $type]) {
            $xml .= '<enclosure url="' . htmlspecialchars($url, ENT_QUOTES) . '" type="' . htmlspecialchars($type, ENT_QUOTES) . '"/>';
        }
        $xml .= '</item>';
        $item = [];
        $mapper->callAddRemoteFiles($item, new \SimpleXMLElement($xml), 'https://feed.test/feed.xml');
        return $item;
    }

    private function getMapper(array $responses, string $importPath = '/uploads/tx_newsimporticsxml/'): XmlMapper
    {
        return new class ($responses, $importPath) extends XmlMapper {
            public array $responses;
            public array $fetched = [];
            public array $written = [];
            public array $existing = [];

            public function __construct(array $responses, string $importPath)
            {
                $this->responses = $responses;
                $this->logger = new NullLogger();
                $this->extensionConfiguration = ['importPath' => $importPath];
            }

            public function callAddRemoteFiles(array &$singleItem, \SimpleXMLElement $xml, string $xmlPath): void
            {
                $this->addRemoteFiles($singleItem, $xml, $xmlPath);
            }

            protected function getPublicPath(): string
            {
                return '/public';
            }

            protected function fileExists(string $absoluteFile): bool
            {
                return isset($this->existing[$absoluteFile]);
            }

            protected function fetchUrl(string $url)
            {
                $this->fetched[] = $url;
                return $this->responses[$url] ?? false;
            }

            protected function writeFile(string $absoluteFile, string $content): bool
            {
                $this->written[$absoluteFile] = $content;
                return true;
            }
        };
    }
}
