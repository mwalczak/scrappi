<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\NetflixVideo\Command\CreateNetflixVideoCommand;
use App\Domain\NetflixVideo\Entity\NetflixVideo as NetflixVideoEntity;
use App\Infrastructure\ApiPlatform\Mapper\NetflixVideoMapper;
use App\Infrastructure\ApiPlatform\Resource\NetflixVideo;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @implements ProcessorInterface<NetflixVideo, NetflixVideo>
 */
final readonly class NetflixVideoProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {
    }

    /**
     * @param NetflixVideo $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): NetflixVideo
    {
        $command = new CreateNetflixVideoCommand(
            title: $data->title,
            description: $data->description,
            releaseYear: $data->releaseYear,
            imdbRating: $data->imdbRating
        );

        $envelope = $this->messageBus->dispatch($command);

        /** @var HandledStamp $handledStamp */
        $handledStamp = $envelope->last(HandledStamp::class);
        /** @var NetflixVideoEntity $video */
        $video = $handledStamp->getResult();

        return NetflixVideoMapper::fromEntity($video);
    }
}
