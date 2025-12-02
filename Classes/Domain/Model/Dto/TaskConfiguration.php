<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Domain\Model\Dto;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration of the import task
 */
class TaskConfiguration
{
    protected string $email = '';
    protected string $path = '';
    protected string $mapping = '';
    protected string $format = '';
    protected int $pid = 0;
    protected bool $persistAsExternalUrl = false;
    protected bool $cleanBeforeImport = false;
    protected bool $setSlug = false;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getMapping(): string
    {
        return $this->mapping;
    }

    public function setMapping(string $mapping): void
    {
        $this->mapping = $mapping;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    public function isPersistAsExternalUrl(): bool
    {
        return $this->persistAsExternalUrl;
    }

    public function setPersistAsExternalUrl(bool $persistAsExternalUrl): void
    {
        $this->persistAsExternalUrl = $persistAsExternalUrl;
    }

    public function getCleanBeforeImport(): bool
    {
        return $this->cleanBeforeImport;
    }

    public function setCleanBeforeImport(bool $cleanBeforeImport): void
    {
        $this->cleanBeforeImport = $cleanBeforeImport;
    }

    public function isSetSlug(): bool
    {
        return $this->setSlug;
    }

    public function setSetSlug(bool $setSlug): void
    {
        $this->setSlug = $setSlug;
    }

    /**
     * Split the configuration from multiline to array
     * 123:This is a category title
     * 345:And another one
     */
    public function getMappingConfigured(): array
    {
        $out = [];
        $lines = GeneralUtility::trimExplode('|', $this->mapping, true);
        foreach ($lines as $line) {
            $split = GeneralUtility::trimExplode(':', $line, true, 2);
            $out[$split[1]] = $split[0];
        }

        return $out;
    }
}
