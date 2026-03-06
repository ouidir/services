<?php

namespace App\Service\Application;

use App\Entity\Application;
use App\Entity\Gesip;
use App\Entity\SSwitch;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Counts anomalies for applications.
 */
class AnomalyCounter
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function computeAnomalies(Application $application, \DateTime $date): void
    {
        $gesips = $this->em->getRepository(Gesip::class)
            ->getAnomaliesGesipByApplication($application, $date);

        $switches = $this->em->getRepository(SSwitch::class)
            ->getAnomaliesSwitchByApplication($application, $date);

        $counts = $this->countGesipAnomalies($application, $gesips);
        $switchCount = $this->countSwitchAnomalies($application, $switches);

        $application->setNbrSwitchs($switchCount);
        $application->setNbrGesips($counts['gesips']);
        $application->setNbrInformations($counts['informations']);
    }

    /**
     * @param array<Gesip> $gesips
     * @return array{gesips: int, informations: int}
     */
    private function countGesipAnomalies(Application $application, array $gesips): array
    {
        $applicationId = $application->getId();
        $applicationName = $application->getNom();

        $gesipCount = 0;
        $informationCount = 0;

        foreach ($gesips as $gesip) {
            if ($applicationId !== $gesip->getApplication()->getId()) {
                continue;
            }

            if ($applicationName !== $gesip->getPrincipale()) {
                $informationCount++;
            } else {
                $gesipCount++;
            }
        }

        return [
            'gesips' => $gesipCount,
            'informations' => $informationCount,
        ];
    }

    /**
     * @param array<SSwitch> $switches
     */
    private function countSwitchAnomalies(Application $application, array $switches): int
    {
        $applicationId = $application->getId();

        return count(array_filter(
            $switches,
            fn(SSwitch $switch) => $applicationId === $switch->getApplication()->getId()
        ));
    }
}
