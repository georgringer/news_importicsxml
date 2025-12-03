<?php

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Jobs;

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
use GeorgRinger\NewsImporticsxml\Jobs\ImportJob;
use GeorgRinger\NewsImporticsxml\Mapper\IcsMapper;
use GeorgRinger\NewsImporticsxml\Mapper\XmlMapper;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\TestingFramework\Core\BaseTestCase;

class ImportJobTest extends BaseTestCase
{
    protected $mockedJob;
    protected $mockedSlugHelper;

    public function setUp(): void
    {
        $logger = new NullLogger();

        $this->mockedJob = $this->getAccessibleMock(
            ImportJob::class,
            ['import'],
            [],
            '',
            false
        );
        $this->mockedJob->_set('logger', $logger);

        $this->mockedSlugHelper = $this->getAccessibleMock(
            SlugHelper::class,
            null,
            [],
            '',
            false
        );

        parent::setUp();
    }

    /**
     * @test
     */
    public function xmlMapperIsCalledWithXmlConfiguration()
    {
        $configuration = new TaskConfiguration();
        $configuration->setFormat('xml');
        $this->mockedJob->_set('configuration', $configuration);

        $xmlMapper = $this->getAccessibleMock(XmlMapper::class, ['map'], [], '', false);
        $xmlMapper->_set('slugHelper', $this->mockedSlugHelper);
        $this->mockedJob->_set('xmlMapper', $xmlMapper);

        $xmlMapper->expects(self::once())->method('map');

        $this->mockedJob->_call('run');
    }

    /**
     * @test
     */
    public function icsMapperIsCalledWithXmlConfiguration()
    {
        $configuration = new TaskConfiguration();
        $configuration->setFormat('ics');
        $this->mockedJob->_set('configuration', $configuration);

        $icsMapper = $this->getAccessibleMock(IcsMapper::class, ['map'], [], '', false);
        $icsMapper->_set('slugHelper', $this->mockedSlugHelper);
        $this->mockedJob->_set('icsMapper', $icsMapper);

        $icsMapper->expects(self::once())->method('map');

        $this->mockedJob->_call('run');
    }

    /**
     * @test
     */
    public function nonSupportedMapperThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);
        $configuration = new TaskConfiguration();
        $configuration->setFormat('fo');
        $this->mockedJob->_set('configuration', $configuration);

        $this->mockedJob->_call('run');
    }
}
