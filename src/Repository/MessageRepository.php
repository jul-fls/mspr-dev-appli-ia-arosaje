<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findUnreadMessages(int $user_id): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.conversation', 'c')
            ->where('m.view_at IS NULL')
            ->andWhere('c.from_user = :user_id OR c.to_user = :user_id')
            ->andWhere('m.sender != :user_id')
            ->setParameter('user_id', $user_id)
            ->getQuery()
            ->getResult();
    }


    public function markMessagesAsRead(?int $conversation_id, int $user_id)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->update()
            ->set('m.view_at', 'CURRENT_TIMESTAMP()')
            ->where('m.conversation = :conversation_id')
            ->andWhere('m.sender != :user_id')
            ->andWhere('m.view_at IS NULL')
            ->setParameter('conversation_id', $conversation_id)
            ->setParameter('user_id', $user_id)
            ->getQuery()
            ->execute();
    }
}
