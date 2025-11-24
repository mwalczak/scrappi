<?php

declare(strict_types=1);

namespace App\Infrastructure\External\JustWatch;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\Service\NetflixScraperInterface;
use App\Domain\NetflixVideo\ValueObject\Country;
use App\Domain\NetflixVideo\ValueObject\ImdbId;
use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use App\Infrastructure\External\JustWatch\GraphQL\JustWatchGraphQLClient;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

final readonly class JustWatchNetflixScraper implements NetflixScraperInterface
{
    private const int DAYS_TO_SCRAPE = 7;

    public function __construct(
        private JustWatchGraphQLClient $graphQLClient,
        private LoggerInterface $logger
    ) {
    }

    public function scrapeNetflixReleases(Country $country, int $limit = 50): array
    {
        $this->logger->info('Scraping Netflix releases', [
            'country' => $country->code(),
            'limit' => $limit,
        ]);

        $allVideos = [];
        $videosRemaining = $limit;
        $startDate = new DateTimeImmutable('now');

        for ($dayOffset = 0; $dayOffset < self::DAYS_TO_SCRAPE && $videosRemaining > 0; $dayOffset++) {
            $date = $startDate->modify("-$dayOffset days")->format('Y-m-d');

            $this->logger->info('Scraping day', [
                'date' => $date,
                'remaining' => $videosRemaining,
            ]);

            $videos = $this->scrapeNetflixReleasesForDate($country, $date, $videosRemaining);

            if (empty($videos)) {
                $this->logger->info('No videos found for date, continuing', ['date' => $date]);
                continue;
            }

            foreach ($videos as $video) {
                $allVideos[] = $video;
                $videosRemaining--;

                if ($videosRemaining <= 0) {
                    break;
                }
            }
        }

        $this->logger->info('Scraping completed', [
            'country' => $country->code(),
            'totalVideos' => count($allVideos),
            'daysScraped' => min($dayOffset + 1, self::DAYS_TO_SCRAPE),
        ]);

        return $allVideos;
    }

    /**
     * @return array<NetflixVideo>
     */
    private function scrapeNetflixReleasesForDate(Country $country, string $date, int $limit): array
    {
        try {
            $data = $this->graphQLClient->fetchNewTitles($country, $date, $limit);

            if (!isset($data['data']) || !is_array($data['data'])) {
                return [];
            }

            if (!isset($data['data']['newTitles']) || !is_array($data['data']['newTitles'])) {
                return [];
            }

            $edges = $data['data']['newTitles']['edges'] ?? [];
            if (!is_array($edges)) {
                return [];
            }

            return $this->mapGraphQLResponseToEntities($edges);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to scrape Netflix releases for date', [
                'country' => $country->code(),
                'date' => $date,
                'limit' => $limit,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }


    /**
     * @param array<mixed> $edges
     * @return array<NetflixVideo>
     */
    private function mapGraphQLResponseToEntities(array $edges): array
    {
        $entities = [];

        foreach ($edges as $edge) {
            if (!is_array($edge) || !isset($edge['node']) || !is_array($edge['node'])) {
                continue;
            }
            $entity = $this->mapGraphQLNodeToEntity($edge['node']);
            if ($entity !== null) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    /**
     * @param array<mixed> $node
     */
    private function mapGraphQLNodeToEntity(array $node): ?NetflixVideo
    {
        try {
            $content = $node['content'] ?? [];

            if (!is_array($content)) {
                return null;
            }

            return new NetflixVideo(
                VideoId::generate(),
                $this->extractTitle($content),
                $this->extractDescription($content),
                $this->extractReleaseYear($content),
                $this->extractImdbRating($content),
                $this->extractImdbId($content)
            );
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to map GraphQL node to entity', [
                'node' => $node,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param array<mixed> $content
     */
    private function extractTitle(array $content): string
    {
        if (isset($content['title']) && is_string($content['title'])) {
            return $content['title'];
        }

        return 'Unknown';
    }

    /**
     * @param array<mixed> $content
     */
    private function extractDescription(array $content): string
    {
        if (isset($content['shortDescription']) && is_string($content['shortDescription'])) {
            return $content['shortDescription'];
        }

        return '';
    }

    /**
     * @param array<mixed> $content
     */
    private function extractReleaseYear(array $content): int
    {
        $currentYear = (int) date('Y');

        if (!isset($content['fullPath']) || !is_string($content['fullPath'])) {
            return $currentYear;
        }

        // Try to extract year from path like "/pl/film/title-2024"
        if (preg_match('/-(\d{4})$/', $content['fullPath'], $matches)) {
            return (int) $matches[1];
        }

        return $currentYear;
    }

    /**
     * @param array<mixed> $content
     */
    private function extractImdbRating(array $content): ?ImdbRating
    {
        if (!isset($content['scoring']) || !is_array($content['scoring'])) {
            return null;
        }

        if (!isset($content['scoring']['imdbScore']) || !is_numeric($content['scoring']['imdbScore'])) {
            return null;
        }

        return ImdbRating::fromFloat((float) $content['scoring']['imdbScore']);
    }

    /**
     * @param array<mixed> $content
     */
    private function extractImdbId(array $content): ?ImdbId
    {
        if (!isset($content['externalIds']) || !is_array($content['externalIds'])) {
            return null;
        }

        if (!isset($content['externalIds']['imdbId']) || !is_string($content['externalIds']['imdbId'])) {
            return null;
        }

        $imdbIdValue = $content['externalIds']['imdbId'];
        if (empty($imdbIdValue)) {
            return null;
        }

        try {
            return ImdbId::fromString($imdbIdValue);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Invalid IMDB ID format', [
                'imdbId' => $imdbIdValue,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
