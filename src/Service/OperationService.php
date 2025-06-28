<?php

/**
 * Operation service.
 */

namespace App\Service;

use App\Dto\OperationListFiltersDto;
use App\Dto\OperationListInputFiltersDto;
use App\Entity\Wallet;
use App\Entity\Operation;
use App\Entity\User;
use App\Repository\OperationRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class OperationService.
 */
class OperationService implements OperationServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService     Category Service
     * @param TagServiceInterface      $tagService          Tag Service
     * @param OperationRepository      $operationRepository Operation repository
     * @param PaginatorInterface       $paginator           Paginator Interface
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService, private readonly TagServiceInterface $tagService, private readonly OperationRepository $operationRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int                          $page    Page number
     * @param User                         $author  User entity
     * @param OperationListInputFiltersDto $filters Filters to apply
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, User $author, OperationListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->operationRepository->queryAll($author, $filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['operation.id', 'operation.title', 'operation.createdAt', 'operation.updatedAt', 'operation.amount', 'operation.category_id'],
                'defaultSortFieldName' => 'operation.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Get paginated list.
     *
     * @param int                          $page    Page number
     * @param OperationListInputFiltersDto $filters Filters to apply
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListForAdmin(int $page, OperationListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->operationRepository->queryAllForAdmin($filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['operation.id', 'operation.title', 'operation.createdAt', 'operation.updatedAt', 'operation.amount', 'operation.category_id'],
                'defaultSortFieldName' => 'operation.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save operation entity.
     *
     * @param Operation $operation Operation entity
     */
    public function save(Operation $operation): void
    {
        $this->operationRepository->save($operation);
    }

    /**
     * Check if the operation can be created based on wallet balance.
     *
     * @param Operation $operation Operation entity
     * @param User      $user      User entity
     *
     * @return bool True if operation can be created
     */
    public function canBeCreated(Operation $operation, User $user): bool
    {
        try {
            $wallet = $operation->getWallet();
            if (null === $wallet) {
                return false;
            }

            $lastBalance = $this->getWalletBalance($wallet, $user);
            $newBalance = $lastBalance + $operation->getAmount();

            return $newBalance >= 0;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Create a new operation if allowed by wallet balance.
     *
     * @param Operation $operation Operation entity
     * @param User      $user      User entity
     *
     * @return bool True on success, false if operation cannot be created
     */
    public function createOperation(Operation $operation, User $user): bool
    {
        if (!$this->canBeCreated($operation, $user)) {
            return false;
        }

        $this->initializeBalance($operation, $user);
        $this->save($operation);

        return true;
    }

    /**
     * The total balance for all wallets.
     *
     * @param User $author User entity
     *
     * @return float Total balance of all wallets
     */
    public function getTotalBalance(User $author): float
    {
        return $this->operationRepository->getTotalBalance($author);
    }

    /**
     * Initialize the balance of the operation based on wallet's current balance.
     *
     * @param Operation $operation Operation entity
     * @param User      $user      User entity
     */
    public function initializeBalance(Operation $operation, User $user): void
    {
        $wallet = $operation->getWallet();

        $lastBalance = $this->getWalletBalance($wallet, $user);
        $operation->setBalance($lastBalance + $operation->getAmount());
    }

    /**
     * Get the current balance of a wallet for a given user.
     *
     * @param Wallet $wallet Wallet entity
     * @param User   $author User entity
     *
     * @return float Wallet balance or 0 if user is not the owner
     */
    public function getWalletBalance(Wallet $wallet, User $author): float
    {
        if ($wallet->getAuthor() !== $author) {
            return 0.0;
        }

        $operation = $this->operationRepository->findLatestForWallet($wallet);

        return $operation ? $operation->getBalance() : 0.0;
    }

    /**
     * Prepare filters for the operations list.
     *
     * @param OperationListInputFiltersDto $filters Raw filters from request
     *
     * @return OperationListFiltersDto Result filters
     */
    public function prepareFilters(OperationListInputFiltersDto $filters): OperationListFiltersDto
    {
        return new OperationListFiltersDto(
            null !== $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null,
        );
    }
}
