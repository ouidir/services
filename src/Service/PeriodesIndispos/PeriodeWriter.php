<?php

namespace App\Service\PeriodesIndispos;

use App\Entity\PeriodesIndispos;
use App\Entity\Scenario;
use App\Model\IntervalHorraire;
use App\Service\PeriodesIndispos\PeriodeIndispoDto;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Implémentation de l'écriture en base de données
 * Respecte SRP : uniquement persistance
 */
class PeriodeWriter implements PeriodeWriterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function writePeriodes(Scenario $scenario, array $periodes): void
    {
        foreach ($periodes as $periodeDTO) {
            $this->writePeriode($scenario, $periodeDTO);
        }

        $this->entityManager->flush();
    }

    private function writePeriode(Scenario $scenario, PeriodeIndispoDto $dto): void
    {
        $periode = new PeriodesIndispos();
        $periode->setIdScenario($scenario);
        $periode->setDebutPeriode($dto->debut);

        $fin = clone $dto->fin;
        if ($fin->format('H:i:s') === '23:59:59') {
            $fin->setTime(24, 0, 0);
        }
        $periode->setFinPeriode($fin);

        $periode->setDuree($dto->getDureeEnSecondes());
        $periode->setEtatInitial($dto->etat);

        $this->entityManager->persist($periode);

        // Mise à jour de la date de fin de période dans le scénario
        $scenario->setDateFinPeriode($fin);
        $this->entityManager->persist($scenario);

        $this->logger->info("Période insérée : état {$dto->etat}");
    }
}
