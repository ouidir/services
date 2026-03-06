<?php

namespace App\Service\Ponderation;

use App\Entity\Selection;
use App\Repository\PonderationRepository;
use App\Repository\SelectionRepository;
use Doctrine\ORM\EntityManagerInterface;

class SelectionManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SelectionRepository $selectionRepository,
        private PonderationRepository $ponderationRepository
    ) {
    }

    public function save(array $data): int
    {
        if (!isset($data['id'], $data['nom'])) {
            return 0;
        }

        $selection = $data['id']
            ? $this->updateExisting($data['id'], $data['nom'])
            : $this->createNew($data['nom']);

        $this->entityManager->persist($selection);
        $this->entityManager->flush();

        return $selection->getId();
    }

    private function updateExisting(int $id, string $nom): Selection
    {
        $selection = $this->selectionRepository->find($id);
        $selection->setNom($nom);
        $selection->setDateDerniereMaj(new \DateTime());
        return $selection;
    }

    private function createNew(string $nom): Selection
    {
        $selection = new Selection();
        $selection->setNom($nom);
        $selection->setDateCreation(new \DateTime());
        return $selection;
    }

    public function remove(int $id): void
    {
        $selection = $this->selectionRepository->find($id);

        if (!$selection) {
            throw new \InvalidArgumentException("Selection with id $id does not exist.");
        }

        foreach ($selection->getIdPonderation() as $ponderation) {
            $selection->removeIdPonderation($ponderation);
        }

        $this->entityManager->remove($selection);
        $this->entityManager->flush();
    }

    public function getPonderationsForm(Selection $selection): array
    {
        $result = [];
        $ponderations = $this->ponderationRepository->findAll();

        foreach ($ponderations as $ponderation) {
            $applicationNom = $ponderation->getIdScenario()->getApplication()->getNom();
            $result[$applicationNom][$ponderation->getId()] = [
                'scenario' => $ponderation->getIdScenario()->getNom(),
                'checked' => $ponderation->getIdSelection()->contains($selection)
            ];
        }

        ksort($result);
        return $result;
    }

    public function savePonderations(Selection $selection, array $ponderationsForm): void
    {
        // Remove all existing
        foreach ($selection->getIdPonderation() as $ponderation) {
            $selection->removeIdPonderation($ponderation);
        }

        // Add new ones
        foreach ($ponderationsForm as $id => $value) {
            $ponderation = $this->ponderationRepository->find($id);
            if ($ponderation) {
                $selection->addIdPonderation($ponderation);
            }
        }

        $this->entityManager->persist($selection);
        $this->entityManager->flush();
    }

    public function getAll(): array
    {
        return $this->selectionRepository->findBy([], ['nom' => 'asc']);
    }

    public function getById(int $id): Selection
    {
        return $this->selectionRepository->find($id);
    }
}
