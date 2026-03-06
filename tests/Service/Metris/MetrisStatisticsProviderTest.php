<?php

namespace App\Tests\Service\Metris;

use App\Repository\PeriodesIndisposRepository;
use App\Service\Metris\MetrisStatisticsProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetrisStatisticsProviderTest extends TestCase
{
    private PeriodesIndisposRepository&MockObject $repository;
    private MetrisStatisticsProvider $provider;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PeriodesIndisposRepository::class);
        $this->provider = new MetrisStatisticsProvider($this->repository);
    }

    public function testGetStatisticsDelegatesToRepository(): void
    {
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-01-31');
        $expected = [
            ['date' => '2024-01-15', 'appli' => 'APP', 'scenario' => 'SC', 'brut' => '99', 'metris_file' => 'PREFIX'],
        ];

        $this->repository
            ->expects($this->once())
            ->method('statistiquesMetris')
            ->with($dateDebut, $dateFin)
            ->willReturn($expected);

        $result = $this->provider->getStatistics($dateDebut, $dateFin);

        $this->assertSame($expected, $result);
    }

    public function testGetStatisticsReturnsEmptyArrayWhenNoData(): void
    {
        $dateDebut = new \DateTime('2024-06-01');
        $dateFin = new \DateTime('2024-06-30');

        $this->repository
            ->method('statistiquesMetris')
            ->willReturn([]);

        $result = $this->provider->getStatistics($dateDebut, $dateFin);

        $this->assertSame([], $result);
    }

    public function testGetStatisticsPassesDateTimesCorrectly(): void
    {
        $dateDebut = new \DateTime('2024-09-01 00:00:00');
        $dateFin = new \DateTime('2024-09-30 23:59:59');

        $this->repository
            ->expects($this->once())
            ->method('statistiquesMetris')
            ->with(
                $this->equalTo($dateDebut),
                $this->equalTo($dateFin)
            )
            ->willReturn([]);

        $this->provider->getStatistics($dateDebut, $dateFin);
    }
}
