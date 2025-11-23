<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\NetflixVideo\Command\ScrapeNetflixVideosCommand;
use App\Application\Shared\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:scrape-netflix',
    description: 'Scrapes Netflix releases from JustWatch and saves them to the database'
)]
final class ScrapeNetflixCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'country',
                InputArgument::REQUIRED,
                'The country code (ISO 3166-1 alpha-2, e.g., US, GB, PL)'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Maximum number of videos to scrape',
                '50'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $countryCode = $input->getArgument('country');
        $limit = $input->getOption('limit');

        if (!is_string($countryCode)) {
            $io->error('Country code must be a string');
            return Command::FAILURE;
        }

        if (!is_numeric($limit) || (int) $limit <= 0) {
            $io->error('Limit must be a positive integer');
            return Command::FAILURE;
        }

        $io->title('Netflix Video Scraper');
        $io->section(sprintf(
            'Scraping Netflix releases for country: %s (limit: %d)',
            strtoupper($countryCode),
            (int) $limit
        ));

        $command = new ScrapeNetflixVideosCommand($countryCode, (int) $limit);

        try {
            $this->commandBus->dispatch($command);

            $io->success(sprintf(
                'Scraping job for country "%s" has been dispatched!',
                strtoupper($countryCode)
            ));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error(sprintf('An error occurred: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
