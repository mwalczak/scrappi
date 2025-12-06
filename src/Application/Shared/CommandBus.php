<?php

declare(strict_types=1);

namespace App\Application\Shared;

/**
 * Port for dispatching commands.
 * This interface is part of the Application layer and has no infrastructure dependencies.
 */
interface CommandBus
{
    /**
     * Dispatches a command to be handled.
     *
     * @param object $command The command to dispatch
     */
    public function dispatch(object $command): void;
}
