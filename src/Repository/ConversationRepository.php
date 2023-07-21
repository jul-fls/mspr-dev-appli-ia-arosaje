<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Plant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 *
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function save(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findConversationByUsersAndPlant(User $user1, User $user2, Plant $plant): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('(c.from_user = :user1 OR c.to_user = :user1)')
            ->andWhere('(c.from_user = :user2 OR c.to_user = :user2)')
            ->andWhere('c.plant_id = :plant')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('plant', $plant)
            ->getQuery()
            ->getOneOrNullResult();
    }    
}
