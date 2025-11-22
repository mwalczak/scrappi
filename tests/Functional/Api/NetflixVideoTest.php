<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NetflixVideoTest extends WebTestCase
{
    private function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface */
        return static::getContainer()->get('doctrine')->getManager();
    }

    public function testGetCollectionReturnsEmptyArray(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/netflix-videos');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('member', $response);
        $this->assertCount(0, $response['member']);
        $this->assertEquals(0, $response['totalItems']);
    }

    public function testGetCollectionReturnsVideos(): void
    {
        $client = static::createClient();

        // Arrange: Create test videos
        $em = $this->getEntityManager();
        $video1 = new NetflixVideo(
            VideoId::generate(),
            'Stranger Things',
            'A group of kids encounter supernatural forces',
            2016,
            ImdbRating::fromFloat(8.7)
        );

        $video2 = new NetflixVideo(
            VideoId::generate(),
            'The Crown',
            'The reign of Queen Elizabeth II',
            2016,
            ImdbRating::fromFloat(8.6)
        );

        $em->persist($video1);
        $em->persist($video2);
        $em->flush();

        // Act
        $client->request('GET', '/api/netflix-videos');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('member', $response);
        $this->assertCount(2, $response['member']);
        $this->assertEquals(2, $response['totalItems']);

        // Check first video
        $firstVideo = $response['member'][0];
        $this->assertEquals('Stranger Things', $firstVideo['title']);
        $this->assertEquals('A group of kids encounter supernatural forces', $firstVideo['description']);
        $this->assertEquals(2016, $firstVideo['releaseYear']);
        $this->assertEquals(8.7, $firstVideo['imdbRating']);
        $this->assertArrayHasKey('createdAt', $firstVideo);
        $this->assertArrayHasKey('updatedAt', $firstVideo);
    }

    public function testGetSingleVideo(): void
    {
        $client = static::createClient();

        // Arrange
        $em = $this->getEntityManager();
        $videoId = VideoId::generate();
        $video = new NetflixVideo(
            $videoId,
            'Breaking Bad',
            'A high school chemistry teacher turned meth manufacturer',
            2008,
            ImdbRating::fromFloat(9.5)
        );

        $em->persist($video);
        $em->flush();

        // Act
        $client->request('GET', '/api/netflix-videos/' . $videoId->value());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Breaking Bad', $response['title']);
        $this->assertEquals('A high school chemistry teacher turned meth manufacturer', $response['description']);
        $this->assertEquals(2008, $response['releaseYear']);
        $this->assertEquals(9.5, $response['imdbRating']);
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

        // Arrange
        $em = $this->getEntityManager();
        $video = new NetflixVideo(
            VideoId::generate(),
            'Unrated Show',
            'A show without an IMDB rating yet',
            2024,
            null
        );

        $em->persist($video);
        $em->flush();

        // Act
        $client->request('GET', '/api/netflix-videos');

        // Assert
        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('member', $response);
        $this->assertCount(1, $response['member']);
        $this->assertEquals(1, $response['totalItems']);
        // When rating is null, API Platform may omit the key entirely
        $this->assertTrue(
            !isset($response['member'][0]['imdbRating']),
            'imdbRating should be null or omitted'
        );
    }
}
