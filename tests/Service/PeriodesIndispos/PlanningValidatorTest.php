<?php

namespace App\Tests\Service\PeriodesIndispos;

use App\Entity\PeriodePlanningIndispo;
use App\Entity\PlanningIndispo;
use App\Entity\Scenario;
use App\Service\PeriodesIndispos\PlanningValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlanningValidatorTest extends TestCase
{
    private PlanningValidator $validator;
    private Scenario&MockObject $scenario;
    private PlanningIndispo&MockObject $planning;

    protected function setUp(): void
    {
        $this->validator = new PlanningValidator();
        $this->scenario = $this->createMock(Scenario::class);
        $this->planning = $this->createMock(PlanningIndispo::class);
    }

    public function testShouldCalculateReturnsFalseWhenScenarioNotActive(): void
    {
        $this->scenario->method('getFlagActif')->willReturn(false);

        $result = $this->validator->shouldCalculate($this->scenario, new \DateTime('2024-01-15'));

        $this->assertFalse($result);
    }

    public function testShouldCalculateReturnsFalseWhenDateBeforeActivation(): void
    {
        $this->scenario->method('getFlagActif')->willReturn(true);
        $this->scenario->method('getDateActivation')
            ->willReturn(new \DateTime('2024-02-01'));

        $result = $this->validator->shouldCalculate($this->scenario, new \DateTime('2024-01-15'));

        $this->assertFalse($result);
    }

    public function testShouldCalculateReturnsFalseWhenNoPlanningIndispo(): void
    {
        $this->scenario->method('getFlagActif')->willReturn(true);
        $this->scenario->method('getDateActivation')->willReturn(null);
        $this->scenario->method('getPlanningIndispo')->willReturn(null);

        $result = $this->validator->shouldCalculate($this->scenario, new \DateTime('2024-01-15'));

        $this->assertFalse($result);
    }

    public function testShouldCalculateReturnsFalseWhenDayNotInPlanning(): void
    {
        $this->scenario->method('getFlagActif')->willReturn(true);
        $this->scenario->method('getDateActivation')->willReturn(null);
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);

        $periode = $this->createMock(PeriodePlanningIndispo::class);
        $periode->method('getJour')->willReturn(3);

        $this->planning->method('getPeriodes')->willReturn([$periode]);

        $monday = new \DateTime('2024-01-15');
        $this->assertSame('1', $monday->format('N'));

        $result = $this->validator->shouldCalculate($this->scenario, $monday);

        $this->assertFalse($result);
    }

    public function testShouldCalculateReturnsTrueWhenDayInPlanning(): void
    {
        $this->scenario->method('getFlagActif')->willReturn(true);
        $this->scenario->method('getDateActivation')->willReturn(null);
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);

        $periode = $this->createMock(PeriodePlanningIndispo::class);
        $periode->method('getJour')->willReturn(1);

        $this->planning->method('getPeriodes')->willReturn([$periode]);

        $monday = new \DateTime('2024-01-15');
        $this->assertSame('1', $monday->format('N'));

        $result = $this->validator->shouldCalculate($this->scenario, $monday);

        $this->assertTrue($result);
    }

    public function testShouldCalculateReturnsTrueWhenActivationDateMatchesOrBefore(): void
    {
        $this->scenario->method('getFlagActif')->willReturn(true);
        $this->scenario->method('getDateActivation')
            ->willReturn(new \DateTime('2024-01-15'));
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);

        $periode = $this->createMock(PeriodePlanningIndispo::class);
        $periode->method('getJour')->willReturn(1);

        $this->planning->method('getPeriodes')->willReturn([$periode]);

        $result = $this->validator->shouldCalculate($this->scenario, new \DateTime('2024-01-15'));

        $this->assertTrue($result);
    }

    public function testShouldCalculateReturnsFalseWhenEmptyPeriodes(): void
    {
        $this->scenario->method('getFlagActif')->willReturn(true);
        $this->scenario->method('getDateActivation')->willReturn(null);
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);

        $this->planning->method('getPeriodes')->willReturn([]);

        $result = $this->validator->shouldCalculate($this->scenario, new \DateTime('2024-01-15'));

        $this->assertFalse($result);
    }

    public function testIsPeriodeValidReturnsFalseWhenDebutAfterFin(): void
    {
        $debut = new \DateTime('2024-01-15 10:00:00');
        $fin = new \DateTime('2024-01-15 08:00:00');

        $result = $this->validator->isPeriodeValid($this->scenario, $debut, $fin);

        $this->assertFalse($result);
    }

    public function testIsPeriodeValidReturnsFalseWhenNoPlanningIndispo(): void
    {
        $this->scenario->method('getPlanningIndispo')->willReturn(null);

        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');

        $result = $this->validator->isPeriodeValid($this->scenario, $debut, $fin);

        $this->assertFalse($result);
    }

    public function testIsPeriodeValidReturnsFalseWhenDayNotInPlanning(): void
    {
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);

        $periode = $this->createMock(PeriodePlanningIndispo::class);
        $periode->method('getJour')->willReturn(3);

        $this->planning->method('getPeriodes')->willReturn([$periode]);

        $monday = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');

        $result = $this->validator->isPeriodeValid($this->scenario, $monday, $fin);

        $this->assertFalse($result);
    }

    public function testIsPeriodeValidReturnsTrueWhenHoursWithinRange(): void
    {
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);

        $heureDebut = new \DateTime('2024-01-15 07:00:00');
        $heureFin = new \DateTime('2024-01-15 18:00:00');

        $periode = $this->createMock(PeriodePlanningIndispo::class);
        $periode->method('getJour')->willReturn(1);
        $periode->method('getHeureDebut')->willReturn($heureDebut);
        $periode->method('getHeureFin')->willReturn($heureFin);

        $this->planning->method('getPeriodes')->willReturn([$periode]);

        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');

        $result = $this->validator->isPeriodeValid($this->scenario, $debut, $fin);

        $this->assertTrue($result);
    }

    public function testIsPeriodeValidReturnsFalseWhenHoursOutsideRange(): void
    {
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);

        $heureDebut = new \DateTime('2024-01-15 09:00:00');
        $heureFin = new \DateTime('2024-01-15 17:00:00');

        $periode = $this->createMock(PeriodePlanningIndispo::class);
        $periode->method('getJour')->willReturn(1);
        $periode->method('getHeureDebut')->willReturn($heureDebut);
        $periode->method('getHeureFin')->willReturn($heureFin);

        $this->planning->method('getPeriodes')->willReturn([$periode]);

        $debut = new \DateTime('2024-01-15 07:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');

        $result = $this->validator->isPeriodeValid($this->scenario, $debut, $fin);

        $this->assertFalse($result);
    }

    public function testIsPeriodeValidReturnsFalseWhenFinExceedsRange(): void
    {
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);

        $heureDebut = new \DateTime('2024-01-15 07:00:00');
        $heureFin = new \DateTime('2024-01-15 17:00:00');

        $periode = $this->createMock(PeriodePlanningIndispo::class);
        $periode->method('getJour')->willReturn(1);
        $periode->method('getHeureDebut')->willReturn($heureDebut);
        $periode->method('getHeureFin')->willReturn($heureFin);

        $this->planning->method('getPeriodes')->willReturn([$periode]);

        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 18:00:00');

        $result = $this->validator->isPeriodeValid($this->scenario, $debut, $fin);

        $this->assertFalse($result);
    }

    public function testIsPeriodeValidReturnsFalseWithEmptyPeriodes(): void
    {
        $this->scenario->method('getPlanningIndispo')->willReturn($this->planning);
        $this->planning->method('getPeriodes')->willReturn([]);

        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');

        $result = $this->validator->isPeriodeValid($this->scenario, $debut, $fin);

        $this->assertFalse($result);
    }
}
