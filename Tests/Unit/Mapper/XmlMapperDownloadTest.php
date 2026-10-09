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
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;

class XmlMapperDownloadTest extends TestCase
{
    #[Test]
    public function completeDownloadIsReturnedAtOnce(): void
    {
        $mapper = $this->getMapper([new Response(200, ['Content-Length' => '4'], 'full')]);

        self::assertSame('full', $mapper->download());
        self::assertSame(1, $mapper->requests);
    }

    #[Test]
    public function truncatedDownloadIsRepeated(): void
    {
        $mapper = $this->getMapper([
            new Response(200, ['Content-Length' => '4'], 'fu'),
            new Response(200, ['Content-Length' => '4'], 'full'),
        ]);

        self::assertSame('full', $mapper->download());
        self::assertSame(2, $mapper->requests);
    }

    #[Test]
    public function downloadFailsAfterThreeIncompleteAttempts(): void
    {
        $truncated = new Response(200, ['Content-Length' => '4'], 'fu');
        $mapper = $this->getMapper([$truncated, $truncated, $truncated, new Response(200, ['Content-Length' => '4'], 'full')]);

        self::assertFalse($mapper->download());
        self::assertSame(3, $mapper->requests);
    }

    #[Test]
    public function failedRequestIsRepeated(): void
    {
        $mapper = $this->getMapper([null, new Response(200, [], 'full')]);

        self::assertSame('full', $mapper->download());
    }

    #[Test]
    public function missingLengthAndCompressedTransfersAreAccepted(): void
    {
        self::assertSame('abc', $this->getMapper([new Response(200, [], 'abc')])->download());
        self::assertSame('abc', $this->getMapper([new Response(200, ['Content-Length' => '99', 'Content-Encoding' => 'gzip'], 'abc')])->download());
    }

    /**
     * @param list<ResponseInterface|null> $responses
     */
    private function getMapper(array $responses): XmlMapper
    {
        return new class ($responses) extends XmlMapper {
            public int $requests = 0;

            public function __construct(private array $responses)
            {
                $this->logger = new NullLogger();
            }

            public function download(): string|false
            {
                return $this->fetchUrl('https://example.org/a.png');
            }

            protected function request(string $url): ?ResponseInterface
            {
                $this->requests++;
                return array_shift($this->responses);
            }
        };
    }
}
