<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Exception;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * The ezplatform:urls:regenerate-aliases Symfony command implementation.
 * Recreates system URL aliases for all existing Locations and cleanups corrupted URL alias nodes.
 */
class RegenerateUrlAliasesCommand extends Command
{
    const DEFAULT_ITERATION_COUNT = 1000;

    const BEFORE_RUNNING_HINTS = <<<EOT
<error>Before you continue:</error>
- Make sure to back up your database.
- Take installation offline, during the script execution the database should not be modified.
- Run this command without memory limit, i.e. processing of 300k Locations can take up to 1 GB of RAM.
- Run this command in production environment using <info>--env=prod</info>
- Manually clear HTTP cache after running this command.
EOT;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(Repository $repository, LoggerInterface $logger = null)
    {
        parent::__construct();

        $this->repository = $repository;
        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $beforeRunningHints = self::BEFORE_RUNNING_HINTS;
        $this
            ->setName('ezplatform:urls:regenerate-aliases')
            ->setDescription(
                'Regenerates Location URL aliases (autogenerated) and cleans up custom Location ' .
                'and global URL aliases stored in the Legacy Storage Engine'
            )
            ->addOption(
                'iteration-count',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Number of Locations fetched into memory and processed at once',
                self::DEFAULT_ITERATION_COUNT
            )
            ->setHelp(
                <<<EOT
{$beforeRunningHints}

The command <info>%command.name%</info> regenerates URL aliases for Locations and cleans up
corrupted URL aliases (pointing to non-existent Locations).
Existing aliases are archived (will redirect to the new ones).

Note: This script can potentially run for a very long time.

Due to performance issues the command does not send any Events.

<comment>HTTP cache needs to be cleared manually after executing this command.</comment>

EOT
            );
    }

    /**
     * Regenerate URL aliases.
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iterationCount = (int)$input->getOption('iteration-count');

        $locationsCount = $this->repository->sudo(
            function (Repository $repository) {
                return $repository->getLocationService()->getAllLocationsCount();
            }
        );

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            sprintf(
                "<info>Found %d Locations.</info>\n%s\n<info>Do you want to proceed? [y/N] </info>",
                $locationsCount,
                self::BEFORE_RUNNING_HINTS
            ),
            false
        );
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $output->writeln('Regenerating System URL aliases...');

        $progressBar = $this->getProgressBar($locationsCount, $output);
        $progressBar->start();

        for ($offset = 0; $offset <= $locationsCount; $offset += $iterationCount) {
            gc_disable();
            $locations = $this->repository->sudo(
                function (Repository $repository) use ($offset, $iterationCount) {
                    return $repository->getLocationService()->loadAllLocations($offset, $iterationCount);
                }
            );
            $this->processLocations($locations, $progressBar);
            gc_enable();
        }
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Done.</info>');

        $output->writeln('<info>Cleaning up corrupted URL aliases...</info>');
        $corruptedAliasesCount = $this->repository->sudo(
            function (Repository $repository) {
                return $repository->getURLAliasService()->deleteCorruptedUrlAliases();
            }
        );
        $output->writeln("<info>Done. Deleted {$corruptedAliasesCount} entries.</info>");
        $output->writeln('<comment>Make sure to clear HTTP cache afterwards.</comment>');
    }

    /**
     * Return configured progress bar helper.
     *
     * @param int $maxSteps
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    protected function getProgressBar($maxSteps, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, $maxSteps);
        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );

        return $progressBar;
    }

    /**
     * Process single results page of fetched Locations.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $locations
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar
     */
    private function processLocations(array $locations, ProgressBar $progressBar)
    {
        $contentList = $this->repository->sudo(
            function (Repository $repository) use ($locations) {
                $contentInfoList = array_map(
                    function (Location $location) {
                        return $location->contentInfo;
                    },
                    $locations
                );

                // load Content list in all languages
                return $repository->getContentService()->loadContentListByContentInfo(
                    $contentInfoList,
                    Language::ALL,
                    false
                );
            }
        );
        foreach ($locations as $location) {
            try {
                // ignore missing Content items
                if (!isset($contentList[$location->contentId])) {
                    continue;
                }

                $content = $contentList[$location->contentId];
                $this->repository->sudo(
                    function (Repository $repository) use ($location, $content) {
                        $repository->getURLAliasService()->refreshSystemUrlAliasesForLocation(
                            $location
                        );
                    }
                );
            } catch (Exception $e) {
                $contentInfo = $location->getContentInfo();
                $msg = sprintf(
                    'Failed processing location %d - [%d] %s (%s: %s)',
                    $location->id,
                    $contentInfo->id,
                    $contentInfo->name,
                    get_class($e),
                    $e->getMessage()
                );
                $this->logger->warning($msg);
                // in debug mode log full exception with a trace
                $this->logger->debug($e);
            } finally {
                $progressBar->advance(1);
            }
        }
    }
}
