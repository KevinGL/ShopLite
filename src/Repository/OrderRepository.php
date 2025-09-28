<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder("o")
            ->where("o.user = :user")
            ->setParameter("user", $user)
            ->orderBy("o.createdAt", "DESC")
            ->getQuery()
            ->getResult();
    }

    public function findByPage(int $page, int &$nbPages): array
    {
        $qb = $this->createQueryBuilder("o");

        $alls = $qb->getQuery()->getResult();
        $nbPages = ceil(count($alls) / $_ENV["LIMIT_PAGES"]);

        return $this->createQueryBuilder("o")
            ->addSelect(
                "CASE 
                    WHEN o.goAt IS NULL THEN 1 
                    WHEN o.deliveredAt IS NULL THEN 2 
                    ELSE 3 
                END AS HIDDEN priority"
            )
            ->orderBy("priority", "ASC")
            ->addOrderBy("o.createdAt", "DESC")
            ->setFirstResult(($page - 1) * $_ENV["LIMIT_PAGES"])
            ->setMaxResults($_ENV["LIMIT_PAGES"])
            ->getQuery()
            ->getResult();
    }
}
