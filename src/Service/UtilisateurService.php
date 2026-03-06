<?php

namespace App\Service;

use App\Entity\Favoris;
use App\Entity\User;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurService
{
    private const PASSWORD_MIN_LENGTH = 8;

    /**
     * Method __construct
     *
     * @return void
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordEncoder
    ) {
    }

    /**
     * Method getFavoritesByUser
     *
     * @param User $user [explicite description]
     *
     * @return void
     */
    public function getFavoritesByUser(User $user): array
    {
        return $this->entityManager->getRepository(Favoris::class)->getFavoris($user);
    }

    /**
     * Method addFavoritesByUser
     *
     * @param User $user [explicite description]
     * @param Application $application [explicite description]
     *
     * @return void
     */
    public function addFavoritesByUser(User $user, Application $application)
    {
        $favori = new Favoris();
        $favori->setUtilisateur($user);
        $favori->setApplication($application);
        $this->entityManager->persist($favori);
        $this->entityManager->flush();
    }

    /**
     * Method removeFavoritesByUser
     *
     * @param Favoris $favoris [explicite description]
     *
     * @return void
     */
    public function removeFavoritesByUser(Favoris $favoris)
    {
        $this->entityManager->remove($favoris);
        $this->entityManager->flush();
    }

    public function addUserAdmin($nom, $prenom, $email, $password)
    {
        $encodedPassword = $this->passwordEncoder->hashPassword(
            new user(),
            $password
        );

        $user = new User();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $user->setActive(true);
        $user->setRole('ROLE_ADMIN');
        $user->setPassword($encodedPassword);
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Valide la force du mot de passe
     */
    public function validatePassword(string $password): array
    {
        $errors = [];

        // Longueur minimale
        if (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $errors[] = sprintf('Le mot de passe doit contenir au moins %d caractères', self::PASSWORD_MIN_LENGTH);
        }

        return $errors;
    }
}
