<?php

namespace App\Service\Statistics\Formatter;

/**
 * Formatter pour les taux d'indisponibilité
 * Respecte SRP : Formatage uniquement
 */
class TauxFormatter implements TauxFormatterInterface
{
    public function sortByName(array $data): array
    {
        usort($data, fn($a, $b) => $a['nom'] <=> $b['nom']);

        return $data;
    }

    public function sortByApplication(array $data): array
    {
        usort($data, function ($a, $b) {
            // Tri par application
            $appCompare = $a['a'] <=> $b['a'];

            if ($appCompare !== 0) {
                return $appCompare;
            }

            // Si même application et scénario existe, tri par scénario
            if (isset($a['s']) && isset($b['s'])) {
                return $a['s'] <=> $b['s'];
            }

            return 0;
        });

        return $data;
    }
}
