<?php

namespace App\Tests\Service\Intervention;

use App\Entity\SSwitch;
use App\Service\Intervention\SwitchDto;
use PHPUnit\Framework\TestCase;

class SwitchDtoTest extends TestCase
{
    private \DateTime $dateDebut;
    private \DateTime $dateFin;

    protected function setUp(): void
    {
        $this->dateDebut = new \DateTime('2024-02-10 09:00:00');
        $this->dateFin = new \DateTime('2024-02-10 11:30:00');
    }

    public function testConstructorSetsAllProperties(): void
    {
        $dto = new SwitchDto(
            id: 5,
            idSwitch: 'SW-5',
            titre: 'Switch titre',
            description: 'Switch description',
            debut: $this->dateDebut,
            fin: $this->dateFin,
            impactee: 'app_impactee',
        );

        $this->assertSame(5, $dto->id);
        $this->assertSame('SW-5', $dto->idSwitch);
        $this->assertSame('Switch titre', $dto->titre);
        $this->assertSame('Switch description', $dto->description);
        $this->assertSame($this->dateDebut, $dto->debut);
        $this->assertSame($this->dateFin, $dto->fin);
        $this->assertSame('app_impactee', $dto->impactee);
    }

    public function testConstructorDefaultImpactee(): void
    {
        $dto = new SwitchDto(
            id: 1,
            idSwitch: 'SW-1',
            titre: 'titre',
            description: 'desc',
            debut: $this->dateDebut,
            fin: $this->dateFin,
        );

        $this->assertSame('', $dto->impactee);
    }

    public function testFromEntityWithoutFormatDates(): void
    {
        $sswitch = $this->createMock(SSwitch::class);
        $sswitch->method('getId')->willReturn(7);
        $sswitch->method('getIdSwitch')->willReturn('SW-7');
        $sswitch->method('getTitre')->willReturn('Switch 7');
        $sswitch->method('getTexte')->willReturn('Description Switch 7');
        $sswitch->method('getDateDebut')->willReturn($this->dateDebut);
        $sswitch->method('getDateFin')->willReturn($this->dateFin);

        $dto = SwitchDto::fromEntity($sswitch, false);

        $this->assertSame(7, $dto->id);
        $this->assertSame('SW-7', $dto->idSwitch);
        $this->assertSame('Switch 7', $dto->titre);
        $this->assertSame('Description Switch 7', $dto->description);
        $this->assertSame($this->dateDebut, $dto->debut);
        $this->assertSame($this->dateFin, $dto->fin);
        $this->assertSame('', $dto->impactee);
    }

    public function testFromEntityWithFormatDates(): void
    {
        $sswitch = $this->createMock(SSwitch::class);
        $sswitch->method('getId')->willReturn(1);
        $sswitch->method('getIdSwitch')->willReturn('SW-1');
        $sswitch->method('getTitre')->willReturn('Titre');
        $sswitch->method('getTexte')->willReturn('Texte');
        $sswitch->method('getDateDebut')->willReturn($this->dateDebut);
        $sswitch->method('getDateFin')->willReturn($this->dateFin);

        $dto = SwitchDto::fromEntity($sswitch, true);

        $this->assertSame('10/02/2024 09:00:00', $dto->debut);
        $this->assertSame('10/02/2024 11:30:00', $dto->fin);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $dto = new SwitchDto(
            id: 3,
            idSwitch: 'SW-3',
            titre: 'Titre test',
            description: 'Description test',
            debut: $this->dateDebut,
            fin: $this->dateFin,
            impactee: 'app1',
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('idSwitch', $array);
        $this->assertArrayHasKey('titre', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('debut', $array);
        $this->assertArrayHasKey('fin', $array);
        $this->assertArrayHasKey('impactee', $array);
        $this->assertSame(3, $array['id']);
        $this->assertSame('SW-3', $array['idSwitch']);
        $this->assertSame('Titre test', $array['titre']);
        $this->assertSame('Description test', $array['description']);
        $this->assertSame($this->dateDebut, $array['debut']);
        $this->assertSame($this->dateFin, $array['fin']);
        $this->assertSame('app1', $array['impactee']);
    }
}
