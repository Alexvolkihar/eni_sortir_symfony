<?php

namespace App\Repository;

use App\Data\SearchEvent;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function Symfony\Component\Clock\now;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les events en lien avec une recherche
     */
    public function searchFind(SearchEvent $search)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e', 'u')
            ->leftJoin('e.members', 'u');

        if (!empty($search->site)) {
            $qb = $qb
                ->andWhere('e.site = :site')
                ->setParameter('site', $search->site);
        }

        if (!empty($search->name)) {
            $qb = $qb
                ->andWhere('e.name LIKE :name')
                ->setParameter('name', $search->name);
        }

        if (empty($search->betweenFirstDate)) {
            $search->betweenFirstDate = new \DateTime("-1 month");
        } else if (date_diff(new \DateTime("-1 month"), $search->betweenFirstDate)->invert == 1) {
            $search->betweenFirstDate = new \DateTime("-1 month");
        }

        $qb = $qb
            ->andWhere('e.startDateTime >= :firstDate')
            ->setParameter('firstDate', $search->betweenFirstDate);

        if (!empty($search->betweenLastDate)) {
            $qb = $qb
                ->andWhere('e.startDateTime <= :lastDate')
                ->setParameter('lastDate', $search->betweenLastDate);
        }

        if ($search->isHost) {
            $qb = $qb
                ->andWhere('e.host = :user')
                ->setParameter('user', $search->user);
        }

        if ($search->passed) {
            $qb = $qb
                ->andWhere('e.startDateTime <= :now')
                ->setParameter('now', now());
        }

        if ($search->isMember && !$search->notMember) {
            $qb = $qb
                ->andWhere('u = :user')
                ->setParameter('user', $search->user);
        }

        if (!$search->isMember && $search->notMember) {
            /** NTM */
        }

        $query = $qb->getQuery();
        $query->setMaxResults(50);
        return $query->getResult();
    }
}
