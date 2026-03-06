<?php

namespace App\Service\Planning\Constants;

class JourSemaine
{
    public const LUNDI = 1;
    public const MARDI = 2;
    public const MERCREDI = 3;
    public const JEUDI = 4;
    public const VENDREDI = 5;
    public const SAMEDI = 6;
    public const DIMANCHE = 7;

    public const JOURS_SEMAINE = [
        self::LUNDI => 'Lundi',
        self::MARDI => 'Mardi',
        self::MERCREDI => 'Mercredi',
        self::JEUDI => 'Jeudi',
        self::VENDREDI => 'Vendredi',
        self::SAMEDI => 'Samedi',
        self::DIMANCHE => 'Dimanche'
    ];

    public static function getLibelle(int $jour): string
    {
        return self::JOURS_SEMAINE[$jour] ?? '';
    }

    public static function isValid(int $jour): bool
    {
        return isset(self::JOURS_SEMAINE[$jour]);
    }
}
