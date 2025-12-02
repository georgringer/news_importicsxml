<?php

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Command;

use GeorgRinger\News\Domain\Service\NewsImportService;
use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GeorgRinger\NewsImporticsxml\Jobs\ImportJob;
use GeorgRinger\NewsImporticsxml\Mapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportCommand extends Command
{
    protected LoggerInterface $logger;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    protected function configure()
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, $this->getLabel('path'))
            ->addArgument('pid', InputArgument::REQUIRED, $this->getLabel('pid'))
            ->addArgument('format', InputArgument::REQUIRED, $this->getLabel('format'))
            ->addArgument('slug', InputArgument::OPTIONAL, $this->getLabel('slug'), true)
            ->addArgument('cleanBeforeImport', InputArgument::OPTIONAL, $this->getLabel('cleanBeforeImport'), false)
            ->addArgument('persistAsExternalUrl', InputArgument::OPTIONAL, $this->getLabel('persistAsExternalUrl'), false)
            ->addArgument('email', InputArgument::OPTIONAL, $this->getLabel('email'), '')
            ->addArgument('mapping', InputArgument::OPTIONAL, $this->getLabel('mapping'), '')
            ->setDescription('Import of ICS and XML (RSS) into EXT:news');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $xmlMapper = new Mapper\XmlMapper();
        $icsMapper = new Mapper\IcsMapper();

        $newsImportService = GeneralUtility::makeInstance(NewsImportService::class);
        $importJob = new ImportJob($xmlMapper, $icsMapper, $newsImportService);
        $importJob->setConfiguration($this->createConfiguration($input));
        $importJob->run();

        return 0;
    }

    protected function createConfiguration(InputInterface $input): TaskConfiguration
    {
        $configuration = new TaskConfiguration();
        $configuration->setPath((string)$input->getArgument('path'));
        $configuration->setPid((int)$input->getArgument('pid'));
        $configuration->setFormat((string)$input->getArgument('format'));
        $configuration->setCleanBeforeImport((bool)$input->getArgument('cleanBeforeImport'));
        $configuration->setPersistAsExternalUrl((bool)$input->getArgument('persistAsExternalUrl'));
        $configuration->setEmail((string)$input->getArgument('email'));
        $configuration->setSetSlug((bool)$input->getArgument('slug'));

        $mapping = (string)$input->getArgument('mapping');
        if ($mapping) {
            $mapping = str_replace('|', chr(10), $mapping);
            $configuration->setMapping($mapping);
        }

        return $configuration;
    }

    protected function getLabel(string $key): string
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:news_importicsxml/Resources/Private/Language/locallang.xlf:' . $key);
    }

}
