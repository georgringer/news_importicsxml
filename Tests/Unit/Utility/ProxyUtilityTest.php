<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Utility;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\NewsImporticsxml\Utility\ProxyUtility;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProxyUtilityTest extends TestCase
{
    public static function proxyDataProvider(): array
    {
        $proxy = fn(string $host, int $port, string $user = '', string $pass = ''): array => ['hostname' => $host, 'port' => $port, 'username' => $user, 'password' => $pass];

        return [
            'not configured' => [null, 'https://example.org/feed.xml', null],
            'empty string' => ['', 'https://example.org/feed.xml', null],
            'string with scheme' => ['http://10.10.0.250:3128', 'https://example.org/feed.xml', $proxy('10.10.0.250', 3128)],
            'string without scheme' => ['proxy.local:8080', 'http://example.org/feed.xml', $proxy('proxy.local', 8080)],
            'default port' => ['http://proxy.local', 'http://example.org/feed.xml', $proxy('proxy.local', 3128)],
            'credentials' => ['http://us%40er:p%3Ass@proxy.local:8080', 'http://example.org/', $proxy('proxy.local', 8080, 'us@er', 'p:ss')],
            'array by scheme http' => [['http' => 'http://a:1', 'https' => 'http://b:2'], 'http://example.org/', $proxy('a', 1)],
            'array by scheme https' => [['http' => 'http://a:1', 'https' => 'http://b:2'], 'https://example.org/', $proxy('b', 2)],
            'array without matching scheme' => [['https' => 'http://b:2'], 'http://example.org/', null],
            'no proxy exact host' => [['http' => 'http://a:1', 'no' => ['example.org']], 'http://example.org/', null],
            'no proxy subdomain' => [['http' => 'http://a:1', 'no' => ['.example.org']], 'http://feed.example.org/', null],
            'no proxy other host' => [['http' => 'http://a:1', 'no' => ['example.org']], 'http://notexample.org/', $proxy('a', 1)],
            'no proxy wildcard' => [['http' => 'http://a:1', 'no' => ['*']], 'http://example.org/', null],
        ];
    }

    #[Test]
    #[DataProvider('proxyDataProvider')]
    public function proxyIsResolvedForUrl($configuration, string $url, ?array $expected): void
    {
        self::assertSame($expected, ProxyUtility::resolve($configuration, $url));
    }
}
