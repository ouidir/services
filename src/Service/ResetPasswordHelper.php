<?php

namespace App\Service;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

class ResetPasswordHelper
{
    private const TOKEN_LENGTH = 32;
    private const EXPIRATION_HOURS = 2;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ResetPasswordRequestRepository $repository
    ) {
    }

    public function generateResetToken(User $user): string
    {
        // Supprimer les anciennes demandes pour cet utilisateur
        $this->repository->removeAllForUser($user);

        // Générer le token et le selector
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $selector = bin2hex(random_bytes(10));
        $hashedToken = $this->hashToken($token);

        // Créer l'entité
        $expiresAt = (new \DateTimeImmutable())->modify('+' . self::EXPIRATION_HOURS . ' hours');
        $resetRequest = new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->entityManager->persist($resetRequest);
        $this->entityManager->flush();

        // Retourner le token complet (selector:token)
        return $selector . ':' . $token;
    }

    public function validateTokenAndFetchUser(string $fullToken): ?User
    {
        // Nettoyer les tokens expirés
        $this->repository->removeExpiredRequests();

        // Séparer selector et token
        $parts = explode(':', $fullToken);
        if (count($parts) !== 2) {
            return null;
        }

        [$selector, $token] = $parts;

        // Trouver la demande
        $resetRequest = $this->repository->findOneBySelector($selector);

        if (!$resetRequest || $resetRequest->isExpired()) {
            return null;
        }

        // Vérifier le token
        if (!hash_equals($resetRequest->getHashedToken(), $this->hashToken($token))) {
            return null;
        }

        return $resetRequest->getUser();
    }

    public function removeResetRequest(string $fullToken): void
    {
        $parts = explode(':', $fullToken);
        if (count($parts) !== 2) {
            return;
        }

        $resetRequest = $this->repository->findOneBySelector($parts[0]);
        if ($resetRequest) {
            $this->entityManager->remove($resetRequest);
            $this->entityManager->flush();
        }
    }

    public function getTokenLifetime(): int
    {
        return self::EXPIRATION_HOURS;
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
