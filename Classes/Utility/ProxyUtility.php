<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Utility;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Resolves $GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'] for a single URL
 */
class ProxyUtility
{
    /**
     * @param mixed $proxyConfiguration string or array with the keys http, https and no
     * @return array{hostname: string, port: int, username: string, password: string}|null
     */
    public static function resolve($proxyConfiguration, string $url): ?array
    {
        $urlParts = parse_url($url);
        $host = strtolower((string)($urlParts['host'] ?? ''));
        $scheme = strtolower((string)($urlParts['scheme'] ?? 'http'));

        if (is_array($proxyConfiguration)) {
            if (self::isExcluded($host, (array)($proxyConfiguration['no'] ?? []))) {
                return null;
            }
            $proxyConfiguration = $proxyConfiguration[$scheme] ?? null;
        }
        if (!is_string($proxyConfiguration) || trim($proxyConfiguration) === '') {
            return null;
        }

        $proxyConfiguration = trim($proxyConfiguration);
        if (!str_contains($proxyConfiguration, '://')) {
            $proxyConfiguration = 'http://' . $proxyConfiguration;
        }
        $proxyParts = parse_url($proxyConfiguration);
        if (empty($proxyParts['host'])) {
            return null;
        }

        return [
            'hostname' => $proxyParts['host'],
            'port' => (int)($proxyParts['port'] ?? 0) ?: 3128,
            'username' => rawurldecode((string)($proxyParts['user'] ?? '')),
            'password' => rawurldecode((string)($proxyParts['pass'] ?? '')),
        ];
    }

    protected static function isExcluded(string $host, array $noProxy): bool
    {
        foreach ($noProxy as $entry) {
            $entry = strtolower(trim((string)$entry));
            if ($entry === '') {
                continue;
            }
            if ($entry === '*') {
                return true;
            }
            $entry = ltrim($entry, '.');
            if ($host === $entry || str_ends_with($host, '.' . $entry)) {
                return true;
            }
        }
        return false;
    }
}
