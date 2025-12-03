<?php

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Domain\Model\Dto;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use TYPO3\TestingFramework\Core\BaseTestCase;

class TaskConfigurationTest extends BaseTestCase
{
    protected TaskConfiguration $instance;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->instance = new TaskConfiguration();
        parent::setUp();
    }

    /**
     * @test
     */
    public function emailCanBeSet()
    {
        $value = 'fo@bar.com';
        $this->instance->setEmail($value);
        self::assertEquals($value, $this->instance->getEmail());
    }

    /**
     * @test
     */
    public function pathCanBeSet()
    {
        $value = 'fileadmin/123.xml';
        $this->instance->setPath($value);
        self::assertEquals($value, $this->instance->getPath());
    }

    /**
     * @test
     */
    public function formatCanBeSet()
    {
        $value = 'xml';
        $this->instance->setFormat($value);
        self::assertEquals($value, $this->instance->getFormat());
    }

    /**
     * @test
     */
    public function pidCanBeSet()
    {
        $value = '456';
        $this->instance->setPid($value);
        self::assertEquals($value, $this->instance->getPid());
    }

    /**
     * @test
     */
    public function mappingCanBeSet()
    {
        $value = 'fo:bar';
        $this->instance->setMapping($value);
        self::assertEquals($value, $this->instance->getMapping());
    }
}
