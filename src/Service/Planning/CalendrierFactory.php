<?php

namespace App\Service\Planning;

use App\Entity\Calendrier;
use App\Entity\Periode;
use App\Service\Planning\Constants\JourSemaine;
use Sabberworm\CSS\Value\CalcRuleValueList;

class CalendrierFactory implements CalendrierFactoryInterface
{
    public function createFromPlanning(array $planning): Calendrier
    {
        $calendrier = new Calendrier();
        $calendrier->setLibelle($planning['libelle']);
        $calendrier->setPosition($planning['position'] ?? 0);

        foreach ($planning['periodes'] as $periodeData) {
            $periode = $this->createPeriode($periodeData);
            $calendrier->addPeriode($periode);
        }

        return $calendrier;
    }

    public function createWithDefaultPeriodes(): Calendrier
    {
        $calendrier = new Calendrier();

        for ($jour = JourSemaine::LUNDI; $jour <= JourSemaine::DIMANCHE; $jour++) {
            $periode = $this->createDefaultPeriode($jour);
            $calendrier->addPeriode($periode);
        }
        return $calendrier;
    }

    private function createPeriode(array $periodeData): Periode
    {
        $periode = new Periode();
        $periode->setJour($periodeData['jour']);
        $periode->setHeureDebut(new \DateTime($periodeData['heureDebut']));
        $periode->setHeureFin(new \DateTime($periodeData['heureFin']));
        $periode->setCommentaire($periodeData['commentaire'] ?? '');

        return $periode;
    }

    private function createDefaultPeriode(int $jour): Periode
    {
        $periode = new Periode();
        $periode->setJour($jour);
        $periode->setHeureDebut((new \DateTime())->setTime(0, 0, 0));
        $periode->setHeureFin((new \DateTime())->setTime(23, 59, 59));

        return $periode;
    }
}
