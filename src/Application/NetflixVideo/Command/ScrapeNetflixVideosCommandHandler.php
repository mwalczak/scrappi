<?php

declare(strict_types=1);

namespace App\Application\NetflixVideo\Command;

use App\Domain\NetflixVideo\Repository\NetflixVideoRepositoryInterface;
use App\Domain\NetflixVideo\Service\NetflixScraperInterface;
use App\Domain\NetflixVideo\ValueObject\Country;
use Psr\Log\LoggerInterface;

final readonly class ScrapeNetflixVideosCommandHandler
{
    public function __construct(
        private NetflixScraperInterface $scraper,
        private NetflixVideoRepositoryInterface $repository,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ScrapeNetflixVideosCommand $command): void
    {
        $this->logger->info('Starting Netflix scraping', ['country' => $command->countryCode]);

        try {
            $country = Country::fromString($command->countryCode);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Invalid country code', [
                'country' => $command->countryCode,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $videos = $this->scraper->scrapeNetflixReleases($country, $command->limit);

        if (empty($videos)) {
            $this->logger->warning('No videos found to scrape', [
                'country' => $command->countryCode,
                'limit' => $command->limit,
            ]);
            return;
        }

        $savedCount = 0;
        $skippedCount = 0;
        $stoppedEarly = false;

        foreach ($videos as $video) {
            try {
                // Check if video already exists by IMDB ID
                if ($video->imdbId !== null) {
                    $existingVideo = $this->repository->findByImdbId($video->imdbId);
                    if ($existingVideo !== null) {
                        $this->logger->info('Video already exists, stopping scraping', [
                            'title' => $video->title,
                            'imdbId' => $video->imdbId->value(),
                        ]);
                        $stoppedEarly = true;
                        break;
                    }
                }

                $this->repository->save($video);
                $savedCount++;
                $this->logger->info('Saved video', [
                    'id' => $video->id->value(),
                    'title' => $video->title,
                    'imdbId' => $video->imdbId?->value(),
                ]);
            } catch (\Throwable $e) {
                $skippedCount++;
                $this->logger->error('Failed to save video', [
                    'title' => $video->title,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->logger->info('Scraping completed', [
            'country' => $command->countryCode,
            'limit' => $command->limit,
            'total' => count($videos),
            'saved' => $savedCount,
            'skipped' => $skippedCount,
            'stoppedEarly' => $stoppedEarly,
        ]);
    }
}
