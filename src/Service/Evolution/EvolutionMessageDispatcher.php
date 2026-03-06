<?php

namespace App\Service\Evolution;

use App\Handler\MessageHandler\Message\EvolutionMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Service responsable de la dispatch des messages d'évolution
 */
class EvolutionMessageDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EvolutionDateGenerator $dateGenerator
    ) {
    }

    /**
     * Dispatch les messages d'évolution pour toutes les dates depuis une date de départ
     */
    public function dispatchEvolutionMessages(\DateTime $startDate): void
    {
        $dates = $this->dateGenerator->generateDateList($startDate);

        foreach ($dates as $date) {
            $this->messageBus->dispatch(new EvolutionMessage($date));
        }
    }
}
