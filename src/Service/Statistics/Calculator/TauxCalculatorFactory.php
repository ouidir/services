<?php

namespace App\Service\Statistics\Calculator;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use App\Service\Statistics\Calculator\Strategy\DefaultTauxCalculator;
use App\Service\Statistics\Calculator\Strategy\QuotidienTauxCalculator;
use App\Service\Statistics\Calculator\Strategy\HebdoTauxCalculator;
use App\Service\Statistics\Calculator\Strategy\MensuelleTauxCalculator;
use App\Service\Statistics\Calculator\Strategy\AnnuelleTauxCalculator;
use App\Service\Statistics\DTO\TauxCalculationConfig;

/**
 * Factory pour créer les calculateurs de taux
 * Respecte OCP : Extension par ajout de stratégies
 */
class TauxCalculatorFactory
{
    public function __construct(
        private PeriodesIndisposRepositoryInterface $repository,
    ) {
    }

    public function create(TauxCalculationConfig $config): TauxCalculatorInterface
    {
        return match (true) {
            $config->isVueDefault() => $this->createDefaultCalculator($config->moyenne),
            $config->isVueQuotidienne() => $this->createQuotidienCalculator($config->moyenne),
            $config->isVueHebdomadaire() => $this->createHebdoCalculator($config->moyenne),
            $config->isVueMensuelle() => $this->createMensuelleCalculator($config->moyenne),
            $config->isVueAnnuelle() => $this->createAnnuelleCalculator($config->moyenne),
        };
    }

    private function createDefaultCalculator(bool $moyenne): TauxCalculatorInterface
    {
        if ($moyenne) {
            return new DefaultTauxCalculator($this->repository, moyenne: true);
        }

        return new DefaultTauxCalculator($this->repository, moyenne: false);
    }

    private function createQuotidienCalculator(bool $moyenne): TauxCalculatorInterface
    {
        return new QuotidienTauxCalculator($this->repository, $moyenne);
    }

    private function createHebdoCalculator(bool $moyenne): TauxCalculatorInterface
    {
        return new HebdoTauxCalculator($this->repository, $moyenne);
    }

    private function createMensuelleCalculator(bool $moyenne): TauxCalculatorInterface
    {
        return new MensuelleTauxCalculator($this->repository, $moyenne);
    }

    private function createAnnuelleCalculator(bool $moyenne): TauxCalculatorInterface
    {
        return new AnnuelleTauxCalculator($this->repository, $moyenne);
    }
}
