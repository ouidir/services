<?php

namespace App\Tests\Service\PeriodesIndispos;

use App\Service\PeriodesIndispos\PeriodeIndispoDto;
use PHPUnit\Framework\TestCase;

class PeriodeIndispoDtoTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');

        $dto = new PeriodeIndispoDto($debut, $fin, 0);

        $this->assertSame($debut, $dto->debut);
        $this->assertSame($fin, $dto->fin);
        $this->assertSame(0, $dto->etat);
    }

    public function testConstructorAllowsEqualDebutAndFin(): void
    {
        $date = new \DateTime('2024-01-15 08:00:00');

        $dto = new PeriodeIndispoDto($date, $date, 3);

        $this->assertSame(0, $dto->getDureeEnSecondes());
    }

    public function testConstructorThrowsExceptionWhenDebutAfterFin(): void
    {
        $debut = new \DateTime('2024-01-15 10:00:00');
        $fin = new \DateTime('2024-01-15 08:00:00');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de début doit être antérieure à la date de fin');

        new PeriodeIndispoDto($debut, $fin, 0);
    }

    public function testGetDureeEnSecondesReturnsCorrectDuration(): void
    {
        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');

        $dto = new PeriodeIndispoDto($debut, $fin, 0);

        $this->assertSame(7200, $dto->getDureeEnSecondes());
    }

    public function testGetDureeEnSecondesOneMinute(): void
    {
        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 08:01:00');

        $dto = new PeriodeIndispoDto($debut, $fin, 1);

        $this->assertSame(60, $dto->getDureeEnSecondes());
    }

    public function testGetDureeEnSecondesOneDay(): void
    {
        $debut = new \DateTime('2024-01-15 00:00:00');
        $fin = new \DateTime('2024-01-16 00:00:00');

        $dto = new PeriodeIndispoDto($debut, $fin, 2);

        $this->assertSame(86400, $dto->getDureeEnSecondes());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('etatProvider')]
    public function testConstructorAcceptsVariousEtatValues(int $etat): void
    {
        $debut = new \DateTime('2024-01-15 08:00:00');
        $fin = new \DateTime('2024-01-15 10:00:00');

        $dto = new PeriodeIndispoDto($debut, $fin, $etat);

        $this->assertSame($etat, $dto->etat);
    }

    public static function etatProvider(): array
    {
        return [
            'etat 0 - ok' => [0],
            'etat 1 - degraded' => [1],
            'etat 3 - no execution' => [3],
            'etat 7 - inactive' => [7],
            'etat 9 - maintenance' => [9],
        ];
    }
}
