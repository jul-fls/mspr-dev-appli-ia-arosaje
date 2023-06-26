<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RoleChecker
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function checkUserRole(User $user, string $minRole)
    {
        $user_id = $user->getId();
        $user = $this->entityManager->getRepository('App\Entity\User')->findUserWithRole($user_id);
        $role_min = $this->entityManager->getRepository('App\Entity\Role')->findOneBy(['name' => $minRole]);
        $role_min_power = $role_min->getPowerLevel();
        $role_user = $user->getRole();
        $role_user_power = $role_user->getPowerLevel();
        if ($role_min_power > $role_user_power) {
            throw new AccessDeniedException('Vous n\'avez pas les droits suffisants pour accéder à cette ressource.');
        }

        return true;
    }
}
