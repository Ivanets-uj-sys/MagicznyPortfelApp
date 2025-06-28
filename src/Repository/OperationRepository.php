<?php

/**
 * Operation Repository.
 */

namespace App\Repository;

use App\Entity\Wallet;
use App\Entity\User;
use App\Entity\Operation;
use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use App\Dto\OperationListFiltersDto;

/**
 * Class operationRepository.
 *
 * @extends ServiceEntityRepository<Operation>
 */
class OperationRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    /**
     * Query all records.
     *
     * @param User                    $author  Author
     * @param OperationListFiltersDto $filters Filters to apply
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(User $author, OperationListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('operation')
            ->select('operation', 'category', 'tag')
            ->join('operation.category', 'category')
            ->leftJoin('operation.tags', 'tag')
            ->join('operation.wallet', 'wallet')
            ->where('wallet.author = :author')
            ->setParameter('author', $author);

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * Query all for Admin.
     *
     * @param OperationListFiltersDto $filters Filters to apply
     *
     * @return QueryBuilder Query Builder
     */
    public function queryAllForAdmin(OperationListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('operation')
            ->select('operation', 'category', 'tag')
            ->join('operation.category', 'category')
            ->leftJoin('operation.tags', 'tag')
            ->join('operation.wallet', 'wallet');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * Count operations by category.
     *
     * @param Category $category Category
     *
     * @return int Number of operations in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->createQueryBuilder('operation');

        return $qb->select($qb->expr()->countDistinct('operation.id'))
            ->where('operation.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find the latest operation for a given wallet.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return Operation|null Latest Operation
     */
    public function findLatestForWallet(Wallet $wallet): ?Operation
    {
        return $this->createQueryBuilder('o')
            ->where('o.wallet = :wallet')
            ->setParameter('wallet', $wallet)
            ->orderBy('o.updatedAt', 'DESC')
            ->addOrderBy('o.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * The total balance across all walets.
     *
     * @param User $author User entity
     *
     * @return float The total balance
     */
    public function getTotalBalance(User $author): float
    {
        $walletRepo = $this->getEntityManager()->getRepository(Wallet::class);
        $wallets = $walletRepo->createQueryBuilder('wallet')
            ->where('wallet.author = :author')
            ->setParameter('author', $author)
            ->getQuery()
            ->getResult();

        $totalBalance = 0.0;

        foreach ($wallets as $wallet) {
            $operation = $this->createQueryBuilder('o')
                ->where('o.wallet = :wallet')
                ->setParameter('wallet', $wallet)
                ->orderBy('o.updatedAt', 'DESC')
                ->addOrderBy('o.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if (null !== $operation) {
                $totalBalance += $operation->getBalance();
            }
        }

        return round($totalBalance, 2);
    }

    /**
     * Save entity.
     *
     * @param Operation $operation Operation entity
     */
    public function save(Operation $operation): void
    {
        $this->getEntityManager()->persist($operation);
        $this->getEntityManager()->flush();
    }

    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder            $queryBuilder Query builder
     * @param OperationListFiltersDto $filters      Filters
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, OperationListFiltersDto $filters): QueryBuilder
    {
        if ($filters->category instanceof Category) {
            $queryBuilder->andWhere('category = :category')
                ->setParameter('category', $filters->category);
        }

        if ($filters->tag instanceof Tag) {
            $queryBuilder->andWhere('tag = :tag')
                ->setParameter('tag', $filters->tag);
        }

        return $queryBuilder;
    }
}
