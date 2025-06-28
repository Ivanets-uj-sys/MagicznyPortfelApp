<?php

/**
 * Operation service interface.
 */

namespace App\Service;

use App\Dto\OperationListFiltersDto;
use App\Dto\OperationListInputFiltersDto;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\Wallet;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface OperationServiceInterface.
 */
interface OperationServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int                          $page    Page number
     * @param User                         $author  User Entity
     * @param OperationListInputFiltersDto $filters Filters to apply
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, OperationListInputFiltersDto $filters): PaginationInterface;

    /**
     * Save operation entity.
     *
     * @param Operation $operation Operation entity
     */
    public function save(Operation $operation): void;

    /**
     * The total balance for all wallets.
     *
     * @param User $author User entity
     *
     * @return float Total balance of all wallets
     */
    public function getTotalBalance(User $author): float;

    /**
     * Get the current balance of a wallet for a given user.
     *
     * @param Wallet $wallet Wallet entity
     * @param User   $author User entity
     *
     * @return float Wallet balance or 0 if user is not the owner
     */
    public function getWalletBalance(Wallet $wallet, User $author): float;

    /**
     * Check if the operation can be created based on wallet balance.
     *
     * @param Operation $operation Operation entity
     * @param User      $user      User entity
     *
     * @return bool True if operation can be created
     */
    public function canBeCreated(Operation $operation, User $user): bool;

    /**
     * Create a new operation if allowed by wallet balance.
     *
     * @param Operation $operation Operation entity
     * @param User      $user      User entity
     *
     * @return bool True on success, false if operation cannot be created
     */
    public function createOperation(Operation $operation, User $user): bool;

    /**
     * Initialize the balance of the operation based on wallet's current balance.
     *
     * @param Operation $operation Operation entity
     * @param User      $user      User entity
     */
    public function initializeBalance(Operation $operation, User $user): void;

    /**
     * Prepare filters for the operations list.
     *
     * @param OperationListInputFiltersDto $filters Raw filters from request
     *
     * @return OperationListFiltersDto Result filters
     */
    public function prepareFilters(OperationListInputFiltersDto $filters): OperationListFiltersDto;
}
