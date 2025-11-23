<?php

declare(strict_types=1);

namespace App\Infrastructure\External\JustWatch;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\Service\NetflixScraperInterface;
use App\Domain\NetflixVideo\ValueObject\Country;
use App\Domain\NetflixVideo\ValueObject\ImdbId;
use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class JustWatchNetflixScraper implements NetflixScraperInterface
{
    private const GRAPHQL_URL = 'https://apis.justwatch.com/graphql';
    private const NETFLIX_PACKAGE = 'nfx';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {
    }

    public function scrapeNetflixReleases(Country $country, int $limit = 50): array
    {
        $this->logger->info('Scraping Netflix releases', [
            'country' => $country->code(),
            'limit' => $limit,
        ]);

        try {
            $countryCode = strtoupper($country->code());
            $languageCode = strtolower($country->code());
            $today = date('Y-m-d');

            $response = $this->httpClient->request('POST', self::GRAPHQL_URL, [
                'json' => [
                    'operationName' => 'GetNewTitles',
                    'variables' => [
                        'first' => $limit,
                        'pageType' => 'NEW',
                        'date' => $today,
                        'filter' => [
                            'packages' => [self::NETFLIX_PACKAGE],
                            'excludeIrrelevantTitles' => false,
                        ],
                        'language' => $languageCode,
                        'country' => $countryCode,
                        'priceDrops' => false,
                        'platform' => 'WEB',
                        'showDateBadge' => false,
                        'availableToPackages' => [self::NETFLIX_PACKAGE],
                    ],
                    'query' => $this->getGraphQLQuery(),
                ],
                'headers' => [
                    'Accept' => '*/*',
                    'Content-Type' => 'application/json',
                    'app-version' => '3.13.0-web-web',
                ],
            ]);

            $data = $response->toArray();
            $edges = $data['data']['newTitles']['edges'] ?? [];
            return $this->mapGraphQLResponseToEntities($edges);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to scrape Netflix releases', [
                'country' => $country->code(),
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
            $id = VideoId::generate();
            $content = $node['content'] ?? [];

            if (!is_array($content)) {
                return null;
            }

            $title = 'Unknown';
            if (isset($content['title']) && is_string($content['title'])) {
                $title = $content['title'];
            }

            $description = '';
            if (isset($content['shortDescription']) && is_string($content['shortDescription'])) {
                $description = $content['shortDescription'];
            }

            // Extract release year from fullPath or set current year
            $releaseYear = (int) date('Y');
            if (isset($content['fullPath']) && is_string($content['fullPath'])) {
                // Try to extract year from path like "/pl/film/title-2024"
                if (preg_match('/-(\d{4})$/', $content['fullPath'], $matches)) {
                    $releaseYear = (int) $matches[1];
                }
            }

            $imdbRating = null;
            $imdbId = null;

            if (isset($content['scoring']) && is_array($content['scoring'])) {
                if (isset($content['scoring']['imdbScore']) && is_numeric($content['scoring']['imdbScore'])) {
                    $imdbRating = ImdbRating::fromFloat((float) $content['scoring']['imdbScore']);
                }
            }

            // Extract IMDB ID from externalIds
            if (isset($content['externalIds']) && is_array($content['externalIds'])) {
                if (isset($content['externalIds']['imdbId']) && is_string($content['externalIds']['imdbId'])) {
                    $imdbIdValue = $content['externalIds']['imdbId'];
                    if (!empty($imdbIdValue)) {
                        try {
                            $imdbId = ImdbId::fromString($imdbIdValue);
                        } catch (\InvalidArgumentException $e) {
                            $this->logger->warning('Invalid IMDB ID format', [
                                'imdbId' => $imdbIdValue,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            return new NetflixVideo(
                $id,
                $title,
                $description,
                $releaseYear,
                $imdbRating,
                $imdbId
            );
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to map GraphQL node to entity', [
                'node' => $node,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function getGraphQLQuery(): string
    {
        return <<<'GRAPHQL'
query GetNewTitles($country: Country!, $date: Date!, $language: Language!, $filter: TitleFilter, $after: String, $first: Int! = 10, $priceDrops: Boolean!, $pageType: NewPageType! = NEW) {
  newTitles(
    country: $country
    date: $date
    filter: $filter
    after: $after
    first: $first
    priceDrops: $priceDrops
    pageType: $pageType
  ) {
    totalCount
    edges {
      cursor
      node {
        ... on MovieOrSeason {
          id
          objectId
          objectType
          content(country: $country, language: $language) {
            title
            shortDescription
            fullPath
            scoring {
              imdbVotes
              imdbScore
              tmdbPopularity
              tmdbScore
            }
            externalIds {
              imdbId
            }
            runtime
          }
        }
      }
    }
    pageInfo {
      endCursor
      hasPreviousPage
      hasNextPage
    }
  }
}
GRAPHQL;
    }
}
