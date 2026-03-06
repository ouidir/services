<?php

namespace App\Tests\Service\Intervention;

use App\Entity\Application;
use App\Entity\Gesip;
use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Repository\GesipRepository;
use App\Service\Intervention\GesipDto;
use App\Service\Intervention\GesipService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GesipServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private CacheHandler&MockObject $cacheHandler;
    private GesipService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->cacheHandler = $this->createMock(CacheHandler::class);
        $this->service = new GesipService($this->entityManager, $this->cacheHandler);
    }

    public function testInvalidateCacheClearsCacheWithCorrectTag(): void
    {
        $this->cacheHandler
            ->expects($this->once())
            ->method('clear')
            ->with('intervention_gesip');

        $this->service->invalidateCache();
    }

    public function testGetByApplicationReturnsMappedDtos(): void
    {
        $application = $this->createMock(Application::class);
        $date = new \DateTime('2024-01-15');

        $gesip1 = $this->createMock(Gesip::class);
        $gesip1->method('getId')->willReturn(1);
        $gesip1->method('getIdGesip')->willReturn('G-1');
        $gesip1->method('getLibelle')->willReturn('Titre 1');
        $gesip1->method('getDetail')->willReturn('Detail 1');
        $gesip1->method('getDateDebut')->willReturn(new \DateTime('2024-01-15 08:00:00'));
        $gesip1->method('getDateFin')->willReturn(new \DateTime('2024-01-15 10:00:00'));
        $gesip1->method('getListeAppli')->willReturn(null);
        $gesip1->method('getType')->willReturn(null);
        $gesip1->method('getPrincipale')->willReturn(null);

        $repository = $this->createMock(GesipRepository::class);
        $repository->method('getAnomaliesGesipByApplication')
            ->with($application, $date)
            ->willReturn([$gesip1]);

        $this->entityManager
            ->method('getRepository')
            ->with(Gesip::class)
            ->willReturn($repository);

        $result = $this->service->getByApplication($application, $date);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(GesipDto::class, $result[0]);
        $this->assertSame(1, $result[0]->id);
    }

    public function testGetByApplicationWithFormatDates(): void
    {
        $application = $this->createMock(Application::class);
        $date = new \DateTime('2024-01-15');
        $dateDebut = new \DateTime('2024-01-15 08:00:00');
        $dateFin = new \DateTime('2024-01-15 10:00:00');

        $gesip = $this->createMock(Gesip::class);
        $gesip->method('getId')->willReturn(1);
        $gesip->method('getIdGesip')->willReturn('G-1');
        $gesip->method('getLibelle')->willReturn('Titre');
        $gesip->method('getDetail')->willReturn('Detail');
        $gesip->method('getDateDebut')->willReturn($dateDebut);
        $gesip->method('getDateFin')->willReturn($dateFin);
        $gesip->method('getListeAppli')->willReturn(null);
        $gesip->method('getType')->willReturn(null);
        $gesip->method('getPrincipale')->willReturn(null);

        $repository = $this->createMock(GesipRepository::class);
        $repository->method('getAnomaliesGesipByApplication')->willReturn([$gesip]);

        $this->entityManager->method('getRepository')->willReturn($repository);

        $result = $this->service->getByApplication($application, $date, true);

        $this->assertSame('15/01/2024 08:00:00', $result[0]->debut);
        $this->assertSame('15/01/2024 10:00:00', $result[0]->fin);
    }

    public function testGetByApplicationReturnsEmptyArray(): void
    {
        $application = $this->createMock(Application::class);
        $date = new \DateTime();

        $repository = $this->createMock(GesipRepository::class);
        $repository->method('getAnomaliesGesipByApplication')->willReturn([]);

        $this->entityManager->method('getRepository')->willReturn($repository);

        $result = $this->service->getByApplication($application, $date);

        $this->assertSame([], $result);
    }

    public function testGetAllGroupedByApplicationSkipsGesipWithoutApplication(): void
    {
        $application = $this->createMock(Application::class);
        $application->method('getId')->willReturn(10);

        $gesipWithApp = $this->createMock(Gesip::class);
        $gesipWithApp->method('getApplication')->willReturn($application);
        $gesipWithApp->method('getId')->willReturn(1);
        $gesipWithApp->method('getIdGesip')->willReturn('G-1');
        $gesipWithApp->method('getLibelle')->willReturn('Titre');
        $gesipWithApp->method('getDetail')->willReturn('Detail');
        $gesipWithApp->method('getDateDebut')->willReturn(new \DateTime());
        $gesipWithApp->method('getDateFin')->willReturn(new \DateTime());
        $gesipWithApp->method('getListeAppli')->willReturn(null);
        $gesipWithApp->method('getType')->willReturn(null);
        $gesipWithApp->method('getPrincipale')->willReturn(null);

        $gesipWithoutApp = $this->createMock(Gesip::class);
        $gesipWithoutApp->method('getApplication')->willReturn(null);

        $repository = $this->createMock(GesipRepository::class);
        $repository->method('getAnomaliesGesip')->willReturn([$gesipWithApp, $gesipWithoutApp]);

        $this->entityManager->method('getRepository')->willReturn($repository);

        $result = $this->service->getAllGroupedByApplication();

        $this->assertArrayHasKey(10, $result);
        $this->assertCount(1, $result[10]);
        $this->assertCount(1, $result);
    }

    public function testGetAllGroupedByApplicationGroupsByApplicationId(): void
    {
        $application1 = $this->createMock(Application::class);
        $application1->method('getId')->willReturn(1);

        $application2 = $this->createMock(Application::class);
        $application2->method('getId')->willReturn(2);

        $gesip1 = $this->createMock(Gesip::class);
        $gesip1->method('getApplication')->willReturn($application1);
        $gesip1->method('getId')->willReturn(10);
        $gesip1->method('getIdGesip')->willReturn('G-10');
        $gesip1->method('getLibelle')->willReturn('T1');
        $gesip1->method('getDetail')->willReturn('D1');
        $gesip1->method('getDateDebut')->willReturn(new \DateTime());
        $gesip1->method('getDateFin')->willReturn(new \DateTime());
        $gesip1->method('getListeAppli')->willReturn(null);
        $gesip1->method('getType')->willReturn(null);
        $gesip1->method('getPrincipale')->willReturn(null);

        $gesip2 = $this->createMock(Gesip::class);
        $gesip2->method('getApplication')->willReturn($application2);
        $gesip2->method('getId')->willReturn(20);
        $gesip2->method('getIdGesip')->willReturn('G-20');
        $gesip2->method('getLibelle')->willReturn('T2');
        $gesip2->method('getDetail')->willReturn('D2');
        $gesip2->method('getDateDebut')->willReturn(new \DateTime());
        $gesip2->method('getDateFin')->willReturn(new \DateTime());
        $gesip2->method('getListeAppli')->willReturn(null);
        $gesip2->method('getType')->willReturn(null);
        $gesip2->method('getPrincipale')->willReturn(null);

        $repository = $this->createMock(GesipRepository::class);
        $repository->method('getAnomaliesGesip')->willReturn([$gesip1, $gesip2]);

        $this->entityManager->method('getRepository')->willReturn($repository);

        $result = $this->service->getAllGroupedByApplication();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayHasKey(2, $result);
    }

    public function testGetRssFeedReturnsValueFromCache(): void
    {
        $expectedFeed = [['id' => 1, 'title' => 'Test']];

        $this->cacheHandler
            ->method('get')
            ->with('intervention_gesip', KeysCacheModel::ISAC_GESIP, $this->isType('callable'))
            ->willReturn($expectedFeed);

        $result = $this->service->getRssFeed();

        $this->assertSame($expectedFeed, $result);
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
            ->with('intervention_gesip', KeysCacheModel::ISAC_GESIP, $this->isType('callable'))
            ->willReturn([]);

        $this->service->getRssFeed();
    }

    public function testGetForHistoriqueByDateDelegatesToRepository(): void
    {
        $application = $this->createMock(Application::class);
        $start = new \DateTime('2024-01-01');
        $end = new \DateTime('2024-01-31');
        $expected = [['id' => 1]];

        $repository = $this->createMock(GesipRepository::class);
        $repository
            ->expects($this->once())
            ->method('gesipForHistoriqueByDate')
            ->with($application, $start, $end)
            ->willReturn($expected);

        $this->entityManager
            ->method('getRepository')
            ->with(Gesip::class)
            ->willReturn($repository);

        $result = $this->service->getForHistoriqueByDate($application, $start, $end);

        $this->assertSame($expected, $result);
    }
}
