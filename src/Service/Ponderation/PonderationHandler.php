<?php

namespace App\Service\Ponderation;

use App\Entity\Ponderation;
use Doctrine\ORM\EntityManagerInterface;

final class PonderationHandler
{
    public $id;
    public $scenario;
    public $applicationId;
    public $applicationNom;
    public $totalToutEtat = 0;

    public $totalOk = 0;
    public $totalWarn = 0 ;
    public $totalCritiq = 0 ;

    public $tauxOk = 'N/A';
    public $tauxWarn = 'N/A' ;
    public $tauxCritiq = 'N/A' ;
    public $tauxDispo = 'N/A' ;

    public static $instances = [];

    public function __construct(public Ponderation $ponderation, array $data)
    {
        $this->id               = $data['id_scenario'];
        $this->scenario         = $ponderation->getIdScenario()->getNom();
        $this->applicationId    = $ponderation->getIdScenario()->getApplication()->getId();
        $this->applicationNom   = $ponderation->getIdScenario()->getApplication()->getNom();
    }

    public static function getInstance(EntityManagerInterface $em, array $data): PonderationHandler
    {
        if (!isset(self::$instances[$data['id_scenario']])) {
            $ponderation = $em->getRepository(Ponderation::class)->findOneBy(['idScenario' => $data['id_scenario']]);
            self::$instances[$data['id_scenario']] = new static($ponderation, $data);
        }

        return self::$instances[$data['id_scenario']];
    }

    public function agregate($data)
    {
        if ($this->accept($data)) {
            $heures = $this->ponderation->getHeures();
            if (isset($heures[$data['heure']])) {
                $ponderationHeure = $heures[$data['heure']];
                $this->totalOk      += $data['ok']      * $ponderationHeure;
                $this->totalWarn    += $data['warn']    * $ponderationHeure;
                $this->totalCritiq  += $data['erreur']  * $ponderationHeure;

                $this->totalToutEtat = $this->totalOk + $this->totalWarn + $this->totalCritiq;
            }
        }

        if ($this->totalToutEtat > 0) {
            $this->tauxOk       = $this->getTaux($this->totalOk);
            $this->tauxWarn     = $this->getTaux($this->totalWarn);
            $this->tauxCritiq   = $this->getTaux($this->totalCritiq);
            $this->tauxDispo    = $this->getTaux($this->totalOk + $this->totalWarn);
        }
    }

    private function accept(array $data): bool
    {
        $jour = $data['jour'] === 0 ? 7 : $data['jour'];
        $jour--;
        $jour = \pow(2, $jour);

        if (!((int)$jour & (int)$this->ponderation->getJours())) {
            return false;
        }

        return true;
    }

    public static function getById($id)
    {
        return self::$instances[$id];
    }

    private function getTaux($total)
    {
        return number_format($total * 100 / $this->totalToutEtat, 2, '.', ' ') . ' %';
    }
}
