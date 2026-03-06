<?php

namespace App\Tests\Service\Intervention;

use App\Entity\Application;
use App\Entity\SSwitch;
use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Repository\SSwitchRepository;
use App\Service\Intervention\SwitchDto;
use App\Service\Intervention\SwitchService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SwitchServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private CacheHandler&MockObject $cacheHandler;
    private SwitchService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->cacheHandler = $this->createMock(CacheHandler::class);
        $this->service = new SwitchService($this->entityManager, $this->cacheHandler);
    }

    public function testInvalidateCacheClearsCacheWithCorrectTag(): void
    {
        $this->cacheHandler
            ->expects($this->once())
            ->method('clear')
            ->with('intervention_switch');

        $this->service->invalidateCache();
    }

    public function testGetByApplicationReturnsMappedDtos(): void
    {
        $application = $this->createMock(Application::class);
        $date = new \DateTime('2024-01-15');

        $sswitch = $this->createMock(SSwitch::class);
        $sswitch->method('getId')->willReturn(3);
        $sswitch->method('getIdSwitch')->willReturn('SW-3');
        $sswitch->method('getTitre')->willReturn('Switch 3');
        $sswitch->method('getTexte')->willReturn('Texte 3');
        $sswitch->method('getDateDebut')->willReturn(new \DateTime('2024-01-15 08:00:00'));
        $sswitch->method('getDateFin')->willReturn(new \DateTime('2024-01-15 10:00:00'));

        $repository = $this->createMock(SSwitchRepository::class);
        $repository->method('getAnomaliesSwitchByApplication')
            ->with($application, $date)
            ->willReturn([$sswitch]);

        $this->entityManager
            ->method('getRepository')
            ->with(SSwitch::class)
            ->willReturn($repository);

        $result = $this->service->getByApplication($application, $date);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(SwitchDto::class, $result[0]);
        $this->assertSame(3, $result[0]->id);
    }

    public function testGetByApplicationWithFormatDates(): void
    {
        $application = $this->createMock(Application::class);
        $date = new \DateTime();
        $dateDebut = new \DateTime('2024-03-20 14:00:00');
        $dateFin = new \DateTime('2024-03-20 16:00:00');

        $sswitch = $this->createMock(SSwitch::class);
        $sswitch->method('getId')->willReturn(1);
        $sswitch->method('getIdSwitch')->willReturn('SW-1');
        $sswitch->method('getTitre')->willReturn('T');
        $sswitch->method('getTexte')->willReturn('T');
        $sswitch->method('getDateDebut')->willReturn($dateDebut);
        $sswitch->method('getDateFin')->willReturn($dateFin);

        $repository = $this->createMock(SSwitchRepository::class);
        $repository->method('getAnomaliesSwitchByApplication')->willReturn([$sswitch]);

        $this->entityManager->method('getRepository')->willReturn($repository);

        $result = $this->service->getByApplication($application, $date, true);

        $this->assertSame('20/03/2024 02:00:00', $result[0]->debut);
        $this->assertSame('20/03/2024 04:00:00', $result[0]->fin);
    }

    public function testGetByApplicationReturnsEmptyArray(): void
    {
        $application = $this->createMock(Application::class);
        $date = new \DateTime();

        $repository = $this->createMock(SSwitchRepository::class);
        $repository->method('getAnomaliesSwitchByApplication')->willReturn([]);

        $this->entityManager->method('getRepository')->willReturn($repository);

        $result = $this->service->getByApplication($application, $date);

        $this->assertSame([], $result);
    }

    public function testGetAllGroupedByApplicationSkipsSwitchWithoutApplication(): void
    {
        $application = $this->createMock(Application::class);
        $application->method('getId')->willReturn(5);

        $switchWithApp = $this->createMock(SSwitch::class);
        $switchWithApp->method('getApplication')->willReturn($application);
        $switchWithApp->method('getId')->willReturn(1);
        $switchWithApp->method('getIdSwitch')->willReturn('SW-1');
        $switchWithApp->method('getTitre')->willReturn('T1');
        $switchWithApp->method('getTexte')->willReturn('D1');
        $switchWithApp->method('getDateDebut')->willReturn(new \DateTime());
        $switchWithApp->method('getDateFin')->willReturn(new \DateTime());

        $switchWithoutApp = $this->createMock(SSwitch::class);
        $switchWithoutApp->method('getApplication')->willReturn(null);

        $repository = $this->createMock(SSwitchRepository::class);
        $repository->method('getAnomaliesSwitch')->willReturn([$switchWithApp, $switchWithoutApp]);

        $this->entityManager->method('getRepository')->willReturn($repository);

        $result = $this->service->getAllGroupedByApplication();

        $this->assertCount(1, $result);
        $this->assertArrayHasKey(5, $result);
        $this->assertCount(1, $result[5]);
    }

    public function testGetAllGroupedByApplicationGroupsByApplicationId(): void
    {
        $app1 = $this->createMock(Application::class);
        $app1->method('getId')->willReturn(1);

        $app2 = $this->createMock(Application::class);
        $app2->method('getId')->willReturn(2);

        $switch1 = $this->createMock(SSwitch::class);
        $switch1->method('getApplication')->willReturn($app1);
        $switch1->method('getId')->willReturn(10);
        $switch1->method('getIdSwitch')->willReturn('SW-10');
        $switch1->method('getTitre')->willReturn('T');
        $switch1->method('getTexte')->willReturn('D');
        $switch1->method('getDateDebut')->willReturn(new \DateTime());
        $switch1->method('getDateFin')->willReturn(new \DateTime());

        $switch2 = $this->createMock(SSwitch::class);
        $switch2->method('getApplication')->willReturn($app2);
        $switch2->method('getId')->willReturn(20);
        $switch2->method('getIdSwitch')->willReturn('SW-20');
        $switch2->method('getTitre')->willReturn('T');
        $switch2->method('getTexte')->willReturn('D');
        $switch2->method('getDateDebut')->willReturn(new \DateTime());
        $switch2->method('getDateFin')->willReturn(new \DateTime());

        $repository = $this->createMock(SSwitchRepository::class);
        $repository->method('getAnomaliesSwitch')->willReturn([$switch1, $switch2]);

        $this->entityManager->method('getRepository')->willReturn($repository);

        $result = $this->service->getAllGroupedByApplication();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayHasKey(2, $result);
    }

    public function testGetRssFeedReturnsValueFromCache(): void
    {
        $expected = [['id' => 1, 'title' => 'RSS Item']];

        $this->cacheHandler
            ->method('get')
            ->with('intervention_switch', KeysCacheModel::ISAC_SWITCH, $this->isType('callable'))
            ->willReturn($expected);

        $result = $this->service->getRssFeed();

        $this->assertSame($expected, $result);
    }

    public function testGetRssFeedReturnsEmptyArrayWhenCacheReturnsNull(): void
    {
        $this->cacheHandler
            ->method('get')
            ->willReturn(null);

        $result = $this->service->getRssFeed();

        $this->assertSame([], $result);
    }

    public function testGetRssFeedCallsCacheHandlerWithCorrectParameters(): void
    {
        $this->cacheHandler
            ->expects($this->once())
            ->method('get')
            ->with('intervention_switch', KeysCacheModel::ISAC_SWITCH, $this->isType('callable'))
            ->willReturn([]);

        $this->service->getRssFeed();
    }

    public function testGetForHistoriqueByDateDelegatesToRepository(): void
    {
        $application = $this->createMock(Application::class);
        $start = new \DateTime('2024-02-01');
        $end = new \DateTime('2024-02-28');
        $expected = [['id' => 5, 'switch' => 'SW-5']];

        $repository = $this->createMock(SSwitchRepository::class);
        $repository
            ->expects($this->once())
            ->method('switchForHistoriqueByDate')
            ->with($application, $start, $end)
            ->willReturn($expected);

        $this->entityManager
            ->method('getRepository')
            ->with(SSwitch::class)
            ->willReturn($repository);

        $result = $this->service->getForHistoriqueByDate($application, $start, $end);

        $this->assertSame($expected, $result);
    }
}
