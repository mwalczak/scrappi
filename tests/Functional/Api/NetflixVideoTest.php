<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Domain\NetflixVideo\ValueObject\VideoId;
use App\Tests\Factory\NetflixVideoFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class NetflixVideoTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    public function testGetCollectionReturnsEmptyArray(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/netflix-videos');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = $this->getJsonResponse($client);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('member', $response);
        $this->assertCount(0, $response['member']);
        $this->assertEquals(0, $response['totalItems']);
    }

    public function testGetCollectionReturnsVideos(): void
    {
        $client = static::createClient();

        // Arrange: Create test videos using factory defaults
        $video1 = NetflixVideoFactory::createOne();
        $video2 = NetflixVideoFactory::createOne();

        // Act
        $client->request('GET', '/api/netflix-videos');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = $this->getJsonResponse($client);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('member', $response);
        $this->assertCount(2, $response['member']);
        $this->assertEquals(2, $response['totalItems']);

        // Verify first video structure and data
        $firstVideo = $response['member'][0];
        $this->assertEquals($video1->title, $firstVideo['title']);
        $this->assertEquals($video1->description, $firstVideo['description']);
        $this->assertEquals($video1->releaseYear, $firstVideo['releaseYear']);
        $this->assertEquals($video1->imdbRating?->value(), $firstVideo['imdbRating']);
        $this->assertArrayHasKey('createdAt', $firstVideo);
        $this->assertArrayHasKey('updatedAt', $firstVideo);
    }

    public function testGetSingleVideo(): void
    {
        $client = static::createClient();

        // Arrange: Create video with factory defaults
        $video = NetflixVideoFactory::createOne();

        // Act
        $client->request('GET', '/api/netflix-videos/' . $video->id->value());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = $this->getJsonResponse($client);
        $this->assertEquals($video->title, $response['title']);
        $this->assertEquals($video->description, $response['description']);
        $this->assertEquals($video->releaseYear, $response['releaseYear']);
        $this->assertEquals($video->imdbRating?->value(), $response['imdbRating']);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertArrayHasKey('updatedAt', $response);
    }

    public function testGetNonExistentVideoReturnsNotFound(): void
    {
        $client = static::createClient();

        // Expect error output when 404 exception is thrown
        $this->expectOutputRegex('/.*NotFoundHttpException.*/s');

        $client->request('GET', '/api/netflix-videos/' . VideoId::generate()->value());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testVideosWithNullRating(): void
    {
        $client = static::createClient();

        // Arrange: Create video without rating (essential field for this test)
        $video = NetflixVideoFactory::new()->withoutRating()->create();

        // Act
        $client->request('GET', '/api/netflix-videos');

        // Assert
        $this->assertResponseIsSuccessful();

        $response = $this->getJsonResponse($client);
        $this->assertArrayHasKey('member', $response);
        $this->assertCount(1, $response['member']);
        $this->assertEquals(1, $response['totalItems']);

        // Verify the returned data matches what we created
        $returnedVideo = $response['member'][0];
        $this->assertEquals($video->title, $returnedVideo['title']);
        $this->assertEquals($video->description, $returnedVideo['description']);
        $this->assertEquals($video->releaseYear, $returnedVideo['releaseYear']);

        // When rating is null, API Platform may omit the key entirely
        $this->assertTrue(
            !isset($returnedVideo['imdbRating']),
            'imdbRating should be null or omitted'
        );
    }

    public function testCreateNetflixVideo(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/netflix-videos', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'title' => 'Test Movie',
            'description' => 'A test movie description',
            'releaseYear' => 2024,
            'imdbRating' => 8.5,
        ], JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = $this->getJsonResponse($client);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('Test Movie', $response['title']);
        $this->assertEquals('A test movie description', $response['description']);
        $this->assertEquals(2024, $response['releaseYear']);
        $this->assertEquals(8.5, $response['imdbRating']);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertArrayHasKey('updatedAt', $response);
    }

    public function testCreateNetflixVideoWithoutRating(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/netflix-videos', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'title' => 'Movie Without Rating',
            'description' => 'A movie without IMDB rating',
            'releaseYear' => 2023,
        ], JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCodeSame(201);

        $response = $this->getJsonResponse($client);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('Movie Without Rating', $response['title']);
        $this->assertEquals(2023, $response['releaseYear']);
        $this->assertTrue(
            !isset($response['imdbRating']),
            'imdbRating should be null or omitted'
        );
    }

    public function testCreateNetflixVideoAppearsInCollection(): void
    {
        $client = static::createClient();

        // Create a video via POST
        $client->request('POST', '/api/netflix-videos', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'title' => 'Created Video',
            'description' => 'Video created via API',
            'releaseYear' => 2024,
            'imdbRating' => 7.5,
        ], JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCodeSame(201);
        $createdVideo = $this->getJsonResponse($client);

        // Verify the video appears in the collection
        $client->request('GET', '/api/netflix-videos');
        $this->assertResponseIsSuccessful();

        $response = $this->getJsonResponse($client);
        $this->assertIsArray($response['member']);
        $this->assertCount(1, $response['member']);
        $this->assertIsArray($response['member'][0]);
        $this->assertEquals($createdVideo['id'], $response['member'][0]['id']);
        $this->assertEquals('Created Video', $response['member'][0]['title']);
    }
}
