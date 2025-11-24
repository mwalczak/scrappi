<?php

declare(strict_types=1);

namespace App\Infrastructure\External\JustWatch\GraphQL;

use App\Domain\NetflixVideo\ValueObject\Country;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class JustWatchGraphQLClient
{
    private const GRAPHQL_URL = 'https://apis.justwatch.com/graphql';
    private const API_VERSION = '3.13.0-web-web';
    private const NETFLIX_PACKAGE = 'nfx';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function fetchNewTitles(Country $country, string $date, int $limit): array
    {
        $requestOptions = $this->buildRequest($country, $date, $limit);
        $response = $this->httpClient->request('POST', self::GRAPHQL_URL, $requestOptions);

        return $response->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRequest(Country $country, string $date, int $limit): array
    {
        $countryCode = strtoupper($country->code());
        $languageCode = strtolower($country->code());

        return [
            'json' => [
                'operationName' => 'GetNewTitles',
                'variables' => [
                    'first' => $limit,
                    'pageType' => 'NEW',
                    'date' => $date,
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
                'query' => $this->getQuery(),
            ],
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                'app-version' => self::API_VERSION,
            ],
        ];
    }

    private function getQuery(): string
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
