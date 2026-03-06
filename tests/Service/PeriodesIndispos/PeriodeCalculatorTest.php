<?php

namespace App\Tests\Service\PeriodesIndispos;

use App\Entity\Execution;
use App\Entity\PeriodePlanningIndispo;
use App\Entity\PlanningIndispo;
use App\Entity\Scenario;
use App\Repository\Interface\ExecutionRepositoryInterface;
use App\Service\PeriodesIndispos\PeriodeCalculator;
use App\Service\PeriodesIndispos\PeriodeIndispoDto;
use App\Service\PeriodesIndispos\PlanningValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PeriodeCalculatorTest extends TestCase
{
    private ExecutionRepositoryInterface&MockObject $executionRepository;
    private PlanningValidatorInterface&MockObject $planningValidator;
    private LoggerInterface&MockObject $logger;
    private PeriodeCalculator $calculator;

    protected function setUp(): void
    {
        $this->executionRepository = $this->createMock(ExecutionRepositoryInterface::class);
        $this->planningValidator = $this->createMock(PlanningValidatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->calculator = new PeriodeCalculator(
            $this->executionRepository,
            $this->planningValidator,
            $this->logger
        );
    }

    public function testCalculateReturnsEmptyArrayWhenPlanningValidatorReturnsFalse(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);

        $this->planningValidator->method('shouldCalculate')->willReturn(false);

        $dateDebut = new \DateTime('2024-01-15');
        $dateFin = new \DateTime('2024-01-15');

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertSame([], $result);
    }

    public function testCalculateReturnsEmptyArrayWhenNoPlanningIndispo(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPlanningIndispo')->willReturn(null);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);

        $dateDebut = new \DateTime('2024-01-15');
        $dateFin = new \DateTime('2024-01-15');

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertSame([], $result);
    }

    public function testCalculateSkipsDateWhenAlreadyProcessed(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $dateFinPeriode = new \DateTime('2024-01-20');
        $scenario->method('getDateFinPeriode')->willReturn($dateFinPeriode);

        $this->planningValidator->expects($this->never())->method('shouldCalculate');
        $this->logger->expects($this->atLeastOnce())->method('info');

        $dateDebut = new \DateTime('2024-01-15');
        $dateFin = new \DateTime('2024-01-16');

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertSame([], $result);
    }

    public function testCalculateWithEmptyExecutionsReturnsEmpty(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPasExecution')->willReturn(60);

        $planning = $this->createMock(PlanningIndispo::class);

        $monday = new \DateTime('2024-01-15');
        $this->assertSame('1', $monday->format('N'));

        $heureDebut = new \DateTime('2024-01-15 08:00:00');
        $heureFin = new \DateTime('2024-01-15 18:00:00');

        $periodePlanning = $this->createMock(PeriodePlanningIndispo::class);
        $periodePlanning->method('getJour')->willReturn(1);
        $periodePlanning->method('getHeureDebut')->willReturn($heureDebut);
        $periodePlanning->method('getHeureFin')->willReturn($heureFin);

        $planning->method('getPeriodes')->willReturn([$periodePlanning]);
        $scenario->method('getPlanningIndispo')->willReturn($planning);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);
        $this->executionRepository->method('findByScenarioDateField')->willReturn([]);

        $dateDebut = clone $monday;
        $dateFin = clone $monday;

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertSame([], $result);
    }

    public function testCalculateWithSingleGoodExecutionReturnsOnePeriode(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPasExecution')->willReturn(60);
        $scenario->method('getFlagActif')->willReturn(true);
        $scenario->method('getDateActivation')->willReturn(null);

        $monday = new \DateTime('2024-01-15');
        $heureDebut = new \DateTime('2024-01-15 08:00:00');
        $heureFin = new \DateTime('2024-01-15 09:00:00');

        $periodePlanning = $this->createMock(PeriodePlanningIndispo::class);
        $periodePlanning->method('getJour')->willReturn(1);
        $periodePlanning->method('getHeureDebut')->willReturn($heureDebut);
        $periodePlanning->method('getHeureFin')->willReturn($heureFin);

        $planning = $this->createMock(PlanningIndispo::class);
        $planning->method('getPeriodes')->willReturn([$periodePlanning]);
        $scenario->method('getPlanningIndispo')->willReturn($planning);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);

        $execution = $this->createMock(Execution::class);
        $execution->method('getStatus')->willReturn(0);
        $execution->method('getMaintenance')->willReturn(null);
        $execution->method('getDate')->willReturn(new \DateTime('2024-01-15 08:30:00'));

        $this->executionRepository->method('findByScenarioDateField')
            ->willReturn([$execution]);

        $dateDebut = clone $monday;
        $dateFin = clone $monday;

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(PeriodeIndispoDto::class, $result);
    }

    public function testCalculateMapsPasExecution60Seconds(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPasExecution')->willReturn(60);
        $scenario->method('getFlagActif')->willReturn(true);
        $scenario->method('getDateActivation')->willReturn(null);

        $monday = new \DateTime('2024-01-15');
        $heureDebut = new \DateTime('2024-01-15 08:00:00');
        $heureFin = new \DateTime('2024-01-15 09:00:00');

        $periodePlanning = $this->createMock(PeriodePlanningIndispo::class);
        $periodePlanning->method('getJour')->willReturn(1);
        $periodePlanning->method('getHeureDebut')->willReturn($heureDebut);
        $periodePlanning->method('getHeureFin')->willReturn($heureFin);

        $planning = $this->createMock(PlanningIndispo::class);
        $planning->method('getPeriodes')->willReturn([$periodePlanning]);
        $scenario->method('getPlanningIndispo')->willReturn($planning);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);

        $exec1 = $this->createMock(Execution::class);
        $exec1->method('getStatus')->willReturn(0);
        $exec1->method('getMaintenance')->willReturn(null);
        $exec1->method('getDate')->willReturn(new \DateTime('2024-01-15 08:15:00'));

        $exec2 = $this->createMock(Execution::class);
        $exec2->method('getStatus')->willReturn(0);
        $exec2->method('getMaintenance')->willReturn(null);
        $exec2->method('getDate')->willReturn(new \DateTime('2024-01-15 08:16:00'));

        $this->executionRepository->method('findByScenarioDateField')
            ->willReturn([$exec1, $exec2]);

        $dateDebut = clone $monday;
        $dateFin = clone $monday;

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertNotEmpty($result);
    }

    public function testCalculateWithWarningStatusMapsToOk(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPasExecution')->willReturn(60);
        $scenario->method('getFlagActif')->willReturn(true);
        $scenario->method('getDateActivation')->willReturn(null);

        $monday = new \DateTime('2024-01-15');
        $heureDebut = new \DateTime('2024-01-15 08:00:00');
        $heureFin = new \DateTime('2024-01-15 09:00:00');

        $periodePlanning = $this->createMock(PeriodePlanningIndispo::class);
        $periodePlanning->method('getJour')->willReturn(1);
        $periodePlanning->method('getHeureDebut')->willReturn($heureDebut);
        $periodePlanning->method('getHeureFin')->willReturn($heureFin);

        $planning = $this->createMock(PlanningIndispo::class);
        $planning->method('getPeriodes')->willReturn([$periodePlanning]);
        $scenario->method('getPlanningIndispo')->willReturn($planning);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);

        $execution = $this->createMock(Execution::class);
        $execution->method('getStatus')->willReturn(1);
        $execution->method('getMaintenance')->willReturn(null);
        $execution->method('getDate')->willReturn(new \DateTime('2024-01-15 08:30:00'));

        $this->executionRepository->method('findByScenarioDateField')
            ->willReturn([$execution]);

        $dateDebut = clone $monday;
        $dateFin = clone $monday;

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertNotEmpty($result);
        $etatValues = array_map(fn($p) => $p->etat, $result);
        $this->assertContains(0, $etatValues);
    }

    public function testCalculateWithMaintenanceStatusReturnsMaintenance(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPasExecution')->willReturn(60);
        $scenario->method('getFlagActif')->willReturn(true);
        $scenario->method('getDateActivation')->willReturn(null);

        $monday = new \DateTime('2024-01-15');
        $heureDebut = new \DateTime('2024-01-15 08:00:00');
        $heureFin = new \DateTime('2024-01-15 09:00:00');

        $periodePlanning = $this->createMock(PeriodePlanningIndispo::class);
        $periodePlanning->method('getJour')->willReturn(1);
        $periodePlanning->method('getHeureDebut')->willReturn($heureDebut);
        $periodePlanning->method('getHeureFin')->willReturn($heureFin);

        $planning = $this->createMock(PlanningIndispo::class);
        $planning->method('getPeriodes')->willReturn([$periodePlanning]);
        $scenario->method('getPlanningIndispo')->willReturn($planning);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);

        $execution = $this->createMock(Execution::class);
        $execution->method('getStatus')->willReturn(2);
        $execution->method('getMaintenance')->willReturn(true);
        $execution->method('getDate')->willReturn(new \DateTime('2024-01-15 08:30:00'));

        $this->executionRepository->method('findByScenarioDateField')
            ->willReturn([$execution]);

        $dateDebut = clone $monday;
        $dateFin = clone $monday;

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertNotEmpty($result);
        $etatValues = array_map(fn($p) => $p->etat, $result);
        $this->assertContains(9, $etatValues);
    }

    public function testCalculateSkipsDateAndLogsWhenGapIsMoreThanOneDay(): void
    {
        $dateFinPeriode = new \DateTime('2024-01-20');

        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn($dateFinPeriode);

        $this->logger->expects($this->once())->method('info');

        $dateDebut = new \DateTime('2024-01-15');
        $dateFin = new \DateTime('2024-01-16');

        $this->calculator->calculate($scenario, $dateDebut, $dateFin);
    }

    public function testCalculateHandlesDateRangeSpanningMultipleDays(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPlanningIndispo')->willReturn(null);

        $this->planningValidator->method('shouldCalculate')->willReturn(false);

        $dateDebut = new \DateTime('2024-01-15');
        $dateFin = new \DateTime('2024-01-17');

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertSame([], $result);
    }

    public function testCalculateWithStateChangeBetweenExecutions(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPasExecution')->willReturn(60);
        $scenario->method('getFlagActif')->willReturn(true);
        $scenario->method('getDateActivation')->willReturn(null);

        $monday = new \DateTime('2024-01-15');
        $heureDebut = new \DateTime('2024-01-15 08:00:00');
        $heureFin = new \DateTime('2024-01-15 10:00:00');

        $periodePlanning = $this->createMock(PeriodePlanningIndispo::class);
        $periodePlanning->method('getJour')->willReturn(1);
        $periodePlanning->method('getHeureDebut')->willReturn($heureDebut);
        $periodePlanning->method('getHeureFin')->willReturn($heureFin);

        $planning = $this->createMock(PlanningIndispo::class);
        $planning->method('getPeriodes')->willReturn([$periodePlanning]);
        $scenario->method('getPlanningIndispo')->willReturn($planning);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);

        $exec1 = $this->createMock(Execution::class);
        $exec1->method('getStatus')->willReturn(0);
        $exec1->method('getMaintenance')->willReturn(null);
        $exec1->method('getDate')->willReturn(new \DateTime('2024-01-15 08:30:00'));

        $exec2 = $this->createMock(Execution::class);
        $exec2->method('getStatus')->willReturn(2);
        $exec2->method('getMaintenance')->willReturn(null);
        $exec2->method('getDate')->willReturn(new \DateTime('2024-01-15 08:31:00'));

        $exec3 = $this->createMock(Execution::class);
        $exec3->method('getStatus')->willReturn(0);
        $exec3->method('getMaintenance')->willReturn(null);
        $exec3->method('getDate')->willReturn(new \DateTime('2024-01-15 08:32:00'));

        $this->executionRepository->method('findByScenarioDateField')
            ->willReturn([$exec1, $exec2, $exec3]);

        $dateDebut = clone $monday;
        $dateFin = clone $monday;

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertNotEmpty($result);
        $this->assertGreaterThan(1, count($result));
    }

    public function testCalculateWithExecutionBeforeWindow(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPasExecution')->willReturn(60);
        $scenario->method('getFlagActif')->willReturn(true);
        $scenario->method('getDateActivation')->willReturn(null);

        $monday = new \DateTime('2024-01-15');
        $heureDebut = new \DateTime('2024-01-15 08:00:00');
        $heureFin = new \DateTime('2024-01-15 09:00:00');

        $periodePlanning = $this->createMock(PeriodePlanningIndispo::class);
        $periodePlanning->method('getJour')->willReturn(1);
        $periodePlanning->method('getHeureDebut')->willReturn($heureDebut);
        $periodePlanning->method('getHeureFin')->willReturn($heureFin);

        $planning = $this->createMock(PlanningIndispo::class);
        $planning->method('getPeriodes')->willReturn([$periodePlanning]);
        $scenario->method('getPlanningIndispo')->willReturn($planning);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);

        $execBefore = $this->createMock(Execution::class);
        $execBefore->method('getStatus')->willReturn(2);
        $execBefore->method('getMaintenance')->willReturn(null);
        $execBefore->method('getDate')->willReturn(new \DateTime('2024-01-15 07:58:00'));

        $execInWindow = $this->createMock(Execution::class);
        $execInWindow->method('getStatus')->willReturn(0);
        $execInWindow->method('getMaintenance')->willReturn(null);
        $execInWindow->method('getDate')->willReturn(new \DateTime('2024-01-15 08:30:00'));

        $this->executionRepository->method('findByScenarioDateField')
            ->willReturn([$execBefore, $execInWindow]);

        $dateDebut = clone $monday;
        $dateFin = clone $monday;

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertNotEmpty($result);
    }

    public function testCalculateWithExecutionAfterWindow(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $scenario->method('getDateFinPeriode')->willReturn(null);
        $scenario->method('getPasExecution')->willReturn(60);
        $scenario->method('getFlagActif')->willReturn(true);
        $scenario->method('getDateActivation')->willReturn(null);

        $monday = new \DateTime('2024-01-15');
        $heureDebut = new \DateTime('2024-01-15 08:00:00');
        $heureFin = new \DateTime('2024-01-15 09:00:00');

        $periodePlanning = $this->createMock(PeriodePlanningIndispo::class);
        $periodePlanning->method('getJour')->willReturn(1);
        $periodePlanning->method('getHeureDebut')->willReturn($heureDebut);
        $periodePlanning->method('getHeureFin')->willReturn($heureFin);

        $planning = $this->createMock(PlanningIndispo::class);
        $planning->method('getPeriodes')->willReturn([$periodePlanning]);
        $scenario->method('getPlanningIndispo')->willReturn($planning);

        $this->planningValidator->method('shouldCalculate')->willReturn(true);

        $execInWindow = $this->createMock(Execution::class);
        $execInWindow->method('getStatus')->willReturn(0);
        $execInWindow->method('getMaintenance')->willReturn(null);
        $execInWindow->method('getDate')->willReturn(new \DateTime('2024-01-15 08:30:00'));

        $execAfter = $this->createMock(Execution::class);
        $execAfter->method('getStatus')->willReturn(2);
        $execAfter->method('getMaintenance')->willReturn(null);
        $execAfter->method('getDate')->willReturn(new \DateTime('2024-01-15 09:02:00'));

        $this->executionRepository->method('findByScenarioDateField')
            ->willReturn([$execInWindow, $execAfter]);

        $dateDebut = clone $monday;
        $dateFin = clone $monday;

        $result = $this->calculator->calculate($scenario, $dateDebut, $dateFin);

        $this->assertNotEmpty($result);
    }
}
