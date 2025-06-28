<?php

/**
 * Wallet repository.
 */

namespace App\Repository;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Class WalletRepository.
 *
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * Query all records.
     *
     * @param User $user User entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('wallet')
            ->select('partial wallet.{id, createdAt, updatedAt, title}')
            ->leftjoin('wallet.operations', 'operation')
            ->where('wallet.author = :author')
            ->setParameter('author', $user);
    }

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void
    {
        $this->getEntityManager()->persist($wallet);
        $this->getEntityManager()->flush();
    }
}
