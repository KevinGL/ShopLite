<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findRandom(int $nbItems): array
    {
        $ids = $this->createQueryBuilder('p')
        ->select('p.id')
        ->getQuery()
        ->getScalarResult();

        $ids = array_column($ids, 'id');

        shuffle($ids);
        $randomIds = array_slice($ids, 0, $nbItems);

        return $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $randomIds)
            ->getQuery()
            ->getResult();
    }

    public function findByWords(array $words, int $page, int &$nbPages): array
    {
        $qb = $this->createQueryBuilder('p');

        $orX = $qb->expr()->orX();

        foreach ($words as $i => $word)
        {
            $param = ':word' . $i;
            $orX->add("p.name LIKE $param OR p.description LIKE $param");
            $qb->setParameter($param, '%' . $word . '%');
        }

        $products = $qb->where($orX)->getQuery()->getResult();
        $nbPages = ceil(count($products) / $_ENV["LIMIT_PAGES"]);

        return $qb->where($orX)
            ->setFirstResult(($page - 1) * $_ENV["LIMIT_PAGES"])
            ->setMaxResults($_ENV["LIMIT_PAGES"])
            ->getQuery()
            ->getResult();
    }

    public function findByPage(int $page, int &$nbPages): array
    {
        $alls = $this->createQueryBuilder("p")
            ->getQuery()
            ->getResult();
        
        $nbPages = ceil(count($alls) / $_ENV["LIMIT_PAGES"]);
        
        return $this->createQueryBuilder("p")
            ->setFirstResult(($page - 1) * $_ENV["LIMIT_PAGES"])
            ->setMaxResults($_ENV["LIMIT_PAGES"])
            ->getQuery()
            ->getResult();
    }

    public function findByWordsNoPage(array $words): array
    {
        $qb = $this->createQueryBuilder('p');

        $orX = $qb->expr()->orX();

        foreach ($words as $i => $word)
        {
            $param = ':word' . $i;
            $orX->add("p.name LIKE $param OR p.description LIKE $param");
            $qb->setParameter($param, '%' . $word . '%');
        }

        return $qb->where($orX)
            ->getQuery()
            ->getResult();
    }
}
