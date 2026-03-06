<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Service responsable uniquement de l'export en format JSON
 * Respecte le SRP (Single Responsibility Principle)
 */
class JsonExportService
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Exporte des données en JSON avec groupes de sérialisation
     */
    public function export(
        array $data,
        string $filename,
        array $serializationGroups = []
    ): Response {
        $json = $this->serializer->serialize($data, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'groups' => $serializationGroups
        ]);

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Length', (string) strlen($json));
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s.json"', $filename)
        );

        return $response;
    }
}
