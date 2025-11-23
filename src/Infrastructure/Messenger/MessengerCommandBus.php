<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Application\Shared\CommandBus;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Adapter that implements CommandBus using Symfony Messenger.
 * This is an infrastructure concern and adapts the Symfony Messenger to our application port.
 */
final readonly class MessengerCommandBus implements CommandBus
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {
    }

    public function dispatch(object $command): void
    {
        $this->messageBus->dispatch($command);
    }
}
