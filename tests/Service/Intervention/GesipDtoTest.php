<?php

namespace App\Tests\Service\Intervention;

use App\Entity\Gesip;
use App\Service\Intervention\GesipDto;
use PHPUnit\Framework\TestCase;

class GesipDtoTest extends TestCase
{
    private \DateTime $dateDebut;
    private \DateTime $dateFin;

    protected function setUp(): void
    {
        $this->dateDebut = new \DateTime('2024-01-15 08:00:00');
        $this->dateFin = new \DateTime('2024-01-15 10:00:00');
    }

    public function testConstructorSetsAllProperties(): void
    {
        $dto = new GesipDto(
            id: 1,
            idGesip: 'GESIP-123',
            titre: 'Test titre',
            description: 'Test description',
            debut: $this->dateDebut,
            fin: $this->dateFin,
            impactee: ['app1', 'app2'],
            etat: 'ouvert',
            principale: 'app1',
        );

        $this->assertSame(1, $dto->id);
        $this->assertSame('GESIP-123', $dto->idGesip);
        $this->assertSame('Test titre', $dto->titre);
        $this->assertSame('Test description', $dto->description);
        $this->assertSame($this->dateDebut, $dto->debut);
        $this->assertSame($this->dateFin, $dto->fin);
        $this->assertSame(['app1', 'app2'], $dto->impactee);
        $this->assertSame('ouvert', $dto->etat);
        $this->assertSame('app1', $dto->principale);
    }

    public function testConstructorDefaultValues(): void
    {
        $dto = new GesipDto(
            id: 1,
            idGesip: 'GESIP-123',
            titre: 'titre',
            description: 'desc',
            debut: $this->dateDebut,
            fin: $this->dateFin,
            impactee: null,
        );

        $this->assertNull($dto->etat);
        $this->assertNull($dto->principale);
    }

    public function testFromEntityWithoutFormatDates(): void
    {
        $gesip = $this->createMock(Gesip::class);
        $gesip->method('getId')->willReturn(42);
        $gesip->method('getIdGesip')->willReturn('G-42');
        $gesip->method('getLibelle')->willReturn('Titre GESIP');
        $gesip->method('getDetail')->willReturn('Description GESIP');
        $gesip->method('getDateDebut')->willReturn($this->dateDebut);
        $gesip->method('getDateFin')->willReturn($this->dateFin);
        $gesip->method('getListeAppli')->willReturn(['appA']);
        $gesip->method('getType')->willReturn('planifié');
        $gesip->method('getPrincipale')->willReturn('appA');

        $dto = GesipDto::fromEntity($gesip, false);

        $this->assertSame(42, $dto->id);
        $this->assertSame('G-42', $dto->idGesip);
        $this->assertSame('Titre GESIP', $dto->titre);
        $this->assertSame('Description GESIP', $dto->description);
        $this->assertSame($this->dateDebut, $dto->debut);
        $this->assertSame($this->dateFin, $dto->fin);
        $this->assertSame(['appA'], $dto->impactee);
        $this->assertSame('planifié', $dto->etat);
        $this->assertSame('appA', $dto->principale);
    }

    public function testFromEntityWithFormatDates(): void
    {
        $gesip = $this->createMock(Gesip::class);
        $gesip->method('getId')->willReturn(1);
        $gesip->method('getIdGesip')->willReturn('G-1');
        $gesip->method('getLibelle')->willReturn('Titre');
        $gesip->method('getDetail')->willReturn('Detail');
        $gesip->method('getDateDebut')->willReturn($this->dateDebut);
        $gesip->method('getDateFin')->willReturn($this->dateFin);
        $gesip->method('getListeAppli')->willReturn(null);
        $gesip->method('getType')->willReturn(null);
        $gesip->method('getPrincipale')->willReturn(null);

        $dto = GesipDto::fromEntity($gesip, true);

        $this->assertSame('15/01/2024 08:00:00', $dto->debut);
        $this->assertSame('15/01/2024 10:00:00', $dto->fin);
    }

    public function testToArrayIncludesEtatWhenSet(): void
    {
        $dto = new GesipDto(
            id: 1,
            idGesip: 'G-1',
            titre: 'titre',
            description: 'desc',
            debut: $this->dateDebut,
            fin: $this->dateFin,
            impactee: null,
            etat: 'fermé',
            principale: 'app',
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('etat', $array);
        $this->assertSame('fermé', $array['etat']);
        $this->assertSame(1, $array['id']);
        $this->assertSame('G-1', $array['idGesip']);
        $this->assertSame('titre', $array['titre']);
        $this->assertSame('desc', $array['description']);
        $this->assertSame($this->dateDebut, $array['debut']);
        $this->assertSame($this->dateFin, $array['fin']);
        $this->assertNull($array['impactee']);
        $this->assertSame('app', $array['principale']);
    }

    public function testToArrayExcludesEtatWhenNull(): void
    {
        $dto = new GesipDto(
            id: 1,
            idGesip: 'G-1',
            titre: 'titre',
            description: 'desc',
            debut: $this->dateDebut,
            fin: $this->dateFin,
            impactee: null,
        );

        $array = $dto->toArray();

        $this->assertArrayNotHasKey('etat', $array);
    }
}
