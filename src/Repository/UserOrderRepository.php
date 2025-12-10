<?php

namespace App\Repository;

use App\Entity\UserOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserOrder>
 */
class UserOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserOrder::class);
    }

    public function findFilteredAndSorted(string $status, string $sort)
    {
        $qb = $this->createQueryBuilder('o');

        // FILTER
        if ($status !== 'all') {
            $qb->andWhere('o.order_state = :st')
            ->setParameter('st', $status);
        }

        // SORT MAPPING
        $sortMap = [
            'id' => 'o.id',
            'material' => 'm.name',
            'finish' => 'f.name',
            'color' => 'c.name',
            'user' => 'u.fullName',
            'delivery_date' => 'o.delivery_date',
            'delivery_arrival' => 'o.delivery_arrival',
            'price' => 'o.price_total',
            'quantity' => 'o.quantity',
            'model_multiplier' => 'o.modelMultiplier',
            'delivery_location' => 'o.delivery_location',
            'notes' => 'o.notes',
        ];

        // JOINS needed for sorting
        $qb->leftJoin('o.material', 'm')
        ->leftJoin('o.finish', 'f')
        ->leftJoin('o.color', 'c')
        ->leftJoin('o.user', 'u');

        // SAFE DEFAULT
        $orderBy = $sortMap[$sort] ?? 'o.id';

        $qb->orderBy($orderBy, 'ASC');

        return $qb->getQuery()->getResult();
    }



//    /**
//     * @return UserOrder[] Returns an array of UserOrder objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserOrder
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
