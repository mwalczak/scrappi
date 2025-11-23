<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\NetflixVideo\Command;

use App\Application\NetflixVideo\Command\ScrapeNetflixVideosCommand;
use App\Application\NetflixVideo\Command\ScrapeNetflixVideosCommandHandler;
use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\Repository\NetflixVideoRepositoryInterface;
use App\Domain\NetflixVideo\Service\NetflixScraperInterface;
use App\Domain\NetflixVideo\ValueObject\Country;
use App\Domain\NetflixVideo\ValueObject\ImdbId;
use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ScrapeNetflixVideosCommandHandlerTest extends TestCase
{
    private NetflixScraperInterface&MockObject $scraper;
    private NetflixVideoRepositoryInterface&MockObject $repository;
    private LoggerInterface&MockObject $logger;
    private ScrapeNetflixVideosCommandHandler $handler;

    protected function setUp(): void
    {
        $this->scraper = $this->createMock(NetflixScraperInterface::class);
        $this->repository = $this->createMock(NetflixVideoRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new ScrapeNetflixVideosCommandHandler(
            $this->scraper,
            $this->repository,
            $this->logger
        );
    }

    public function testHandlerScrapesAndSavesVideos(): void
    {
        $command = new ScrapeNetflixVideosCommand('US', 5);

        $video1 = new NetflixVideo(
            VideoId::generate(),
            'Movie 1',
            'Description 1',
            2024,
            ImdbRating::fromFloat(8.0),
            ImdbId::fromString('tt1234567')
        );

        $video2 = new NetflixVideo(
            VideoId::generate(),
            'Movie 2',
            'Description 2',
            2024,
            ImdbRating::fromFloat(7.5),
            ImdbId::fromString('tt2345678')
        );

        $this->scraper
            ->expects($this->once())
            ->method('scrapeNetflixReleases')
            ->with(
                $this->callback(fn(Country $country) => $country->code() === 'US'),
                5
            )
            ->willReturn([$video1, $video2]);

        // Expect both videos to be checked for existence
        $this->repository
            ->expects($this->exactly(2))
            ->method('findByImdbId')
            ->willReturn(null);

        // Expect both videos to be saved
        $this->repository
            ->expects($this->exactly(2))
            ->method('save')
            ->with($this->isInstanceOf(NetflixVideo::class));

        $this->handler->__invoke($command);
    }

    public function testHandlerStopsWhenExistingVideoIsFound(): void
    {
        $command = new ScrapeNetflixVideosCommand('US', 5);

        $video1 = new NetflixVideo(
            VideoId::generate(),
            'New Movie',
            'Description',
            2024,
            ImdbRating::fromFloat(8.0),
            ImdbId::fromString('tt1234567')
        );

        $existingVideo = new NetflixVideo(
            VideoId::generate(),
            'Existing Movie',
            'Description',
            2023,
            ImdbRating::fromFloat(7.0),
            ImdbId::fromString('tt2345678')
        );

        $video2 = new NetflixVideo(
            VideoId::generate(),
            'Movie That Should Not Be Saved',
            'Description',
            2024,
            null,
            ImdbId::fromString('tt3456789')
        );

        // Scraper returns 3 videos
        $this->scraper
            ->expects($this->once())
            ->method('scrapeNetflixReleases')
            ->willReturn([$video1, $existingVideo, $video2]);

        // First video is new, second video exists
        $this->repository
            ->expects($this->exactly(2))
            ->method('findByImdbId')
            ->willReturnOnConsecutiveCalls(
                null,           // First video is new
                $existingVideo  // Second video exists - should stop here
            );

        // Only the first video should be saved
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(NetflixVideo $video) => $video->title === 'New Movie'));

        $this->handler->__invoke($command);
    }

    public function testHandlerHandlesVideosWithoutImdbId(): void
    {
        $command = new ScrapeNetflixVideosCommand('US', 5);

        $videoWithoutImdbId = new NetflixVideo(
            VideoId::generate(),
            'Movie Without IMDB',
            'Description',
            2024,
            ImdbRating::fromFloat(7.0),
            null
        );

        $this->scraper
            ->expects($this->once())
            ->method('scrapeNetflixReleases')
            ->willReturn([$videoWithoutImdbId]);

        // Should not check for existing video since there's no IMDB ID
        $this->repository
            ->expects($this->never())
            ->method('findByImdbId');

        // Should still save the video
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($videoWithoutImdbId);

        $this->handler->__invoke($command);
    }

    public function testHandlerLogsWarningWhenNoVideosFound(): void
    {
        $command = new ScrapeNetflixVideosCommand('US', 5);

        $this->scraper
            ->expects($this->once())
            ->method('scrapeNetflixReleases')
            ->willReturn([]);

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                'No videos found to scrape',
                $this->callback(function ($context) {
                    return $context['country'] === 'US' && $context['limit'] === 5;
                })
            );

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->handler->__invoke($command);
    }

    public function testHandlerContinuesOnSaveError(): void
    {
        $command = new ScrapeNetflixVideosCommand('US', 5);

        $video1 = new NetflixVideo(
            VideoId::generate(),
            'Movie 1',
            'Description 1',
            2024,
            ImdbRating::fromFloat(8.0),
            ImdbId::fromString('tt1234567')
        );

        $video2 = new NetflixVideo(
            VideoId::generate(),
            'Movie 2',
            'Description 2',
            2024,
            ImdbRating::fromFloat(7.5),
            ImdbId::fromString('tt2345678')
        );

        $this->scraper
            ->expects($this->once())
            ->method('scrapeNetflixReleases')
            ->willReturn([$video1, $video2]);

        $this->repository
            ->method('findByImdbId')
            ->willReturn(null);

        // First save throws exception, second should still proceed
        $this->repository
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \RuntimeException('Database error')),
                null
            );

        // Should log the error for the failed save
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Failed to save video',
                $this->callback(fn($context) => $context['title'] === 'Movie 1')
            );

        // Should not throw exception
        $this->handler->__invoke($command);
    }

    public function testHandlerWithInvalidCountryCode(): void
    {
        $command = new ScrapeNetflixVideosCommand('INVALID', 5);

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->__invoke($command);
    }
}
