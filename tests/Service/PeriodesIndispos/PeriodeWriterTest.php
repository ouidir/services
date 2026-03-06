<?php

namespace App\Tests\Service\PeriodesIndispos;

use App\Entity\PeriodesIndispos;
use App\Entity\Scenario;
use App\Service\PeriodesIndispos\PeriodeIndispoDto;
use App\Service\PeriodesIndispos\PeriodeWriter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PeriodeWriterTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;
    private PeriodeWriter $writer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->writer = new PeriodeWriter($this->entityManager, $this->logger);
    }

    public function testWritePeriodesCallsFlushAfterPersisting(): void
    {
        $scenario = $this->createMock(Scenario::class);
        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');
        $dto = new PeriodeIndispoDto($debut, $fin, 0);

        $this->entityManager
            ->expects($this->atLeastOnce())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->writer->writePeriodes($scenario, [$dto]);
    }

    public function testWritePeriodesWithMultipleDtosPersistsAll(): void
    {
        $scenario = $this->createMock(Scenario::class);

        $dto1 = new PeriodeIndispoDto(
            new \DateTime('2024-01-15 08:00:00'),
            new \DateTime('2024-01-15 09:00:00'),
            0
        );
        $dto2 = new PeriodeIndispoDto(
            new \DateTime('2024-01-15 09:00:00'),
            new \DateTime('2024-01-15 10:00:00'),
            3
        );

        $this->entityManager
            ->expects($this->exactly(4))
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->writer->writePeriodes($scenario, [$dto1, $dto2]);
    }

    public function testWritePeriodesWithEmptyArrayOnlyCallsFlush(): void
    {
        $scenario = $this->createMock(Scenario::class);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->writer->writePeriodes($scenario, []);
    }

    public function testWritePeriodeConverts235959ToMidnight(): void
    {
        $scenario = $this->createMock(Scenario::class);

        $debut = new \DateTime('2024-01-15 00:00:00');
        $fin = new \DateTime('2024-01-15 23:59:59');
        $dto = new PeriodeIndispoDto($debut, $fin, 0);

        $capturedPeriode = null;

        $this->entityManager
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$capturedPeriode) {
                if ($entity instanceof PeriodesIndispos) {
                    $capturedPeriode = $entity;
                }
            });

        $this->writer->writePeriodes($scenario, [$dto]);

        $this->assertNotNull($capturedPeriode);
    }

    public function testWritePeriodesLogsInfo(): void
    {
        $scenario = $this->createMock(Scenario::class);

        $dto = new PeriodeIndispoDto(
            new \DateTime('2024-01-15 08:00:00'),
            new \DateTime('2024-01-15 10:00:00'),
            9
        );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('9'));

        $this->writer->writePeriodes($scenario, [$dto]);
    }

    public function testWritePeriodesUpdatesScenarioDateFinPeriode(): void
    {
        $scenario = $this->createMock(Scenario::class);

        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');
        $dto = new PeriodeIndispoDto($debut, $fin, 0);

        $scenario
            ->expects($this->once())
            ->method('setDateFinPeriode');

        $this->writer->writePeriodes($scenario, [$dto]);
    }
}
