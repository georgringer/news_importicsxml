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
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PicoFeed\Reader\Reader;
use PicoFeed\Reader\SubscriptionNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class XmlMapperFeedTest extends TestCase
{
    private const RSS = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>t</title><item><title>a</title></item></channel></rss>';

    public static function setUpBeforeClass(): void
    {
        spl_autoload_register(static function (string $class): void {
            if (str_starts_with($class, 'PicoFeed')) {
                require_once __DIR__ . '/../../../Resources/Private/Contrib/picoFeed/lib/' . str_replace('\\', '/', $class) . '.php';
            }
        });
    }

    #[Test]
    public function feedIsLoadedWithTheEncodingOfTheHeader(): void
    {
        $mapper = $this->getMapper(['http://example.org/feed.xml' => new Response(200, ['Content-Type' => 'application/rss+xml; charset=ISO-8859-1'], self::RSS)]);

        self::assertSame(['http://example.org/feed.xml', self::RSS, 'ISO-8859-1'], $mapper->load('example.org/feed.xml'));
        self::assertSame(['http://example.org/feed.xml'], $mapper->requested);
    }

    #[Test]
    public function feedLinkOfHtmlPageIsFollowed(): void
    {
        $html = '<html><head><link rel="alternate" type="application/rss+xml" href="/feed.xml"></head><body></body></html>';
        $mapper = $this->getMapper([
            'https://example.org/news' => new Response(200, ['Content-Type' => 'text/html'], $html),
            'https://example.org/feed.xml' => new Response(200, ['Content-Type' => 'application/rss+xml'], self::RSS),
        ]);

        self::assertSame(['https://example.org/feed.xml', self::RSS, ''], $mapper->load('https://example.org/news'));
    }

    #[Test]
    public function htmlPageWithoutFeedLinkIsRejected(): void
    {
        $mapper = $this->getMapper(['https://example.org/news' => new Response(200, [], '<html><body>no feed</body></html>')]);

        $this->expectException(SubscriptionNotFoundException::class);
        $mapper->load('https://example.org/news');
    }

    #[Test]
    public function failedDownloadThrows(): void
    {
        $mapper = $this->getMapper([]);

        $this->expectException(RuntimeException::class);
        $mapper->load('https://example.org/feed.xml');
    }

    /**
     * @param array<string, ResponseInterface> $responses
     */
    private function getMapper(array $responses): XmlMapper
    {
        return new class ($responses) extends XmlMapper {
            public array $requested = [];

            public function __construct(private array $responses)
            {
                $this->logger = new NullLogger();
            }

            public function load(string $path): array
            {
                return $this->loadFeed(new Reader(), $path);
            }

            protected function request(string $url): ?ResponseInterface
            {
                $this->requested[] = $url;
                return $this->responses[$url] ?? null;
            }
        };
    }
}
