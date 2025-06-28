<?php

/**
 * Wallet service interface.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Wallet;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface WalletServiceInterface.
 */
interface WalletServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int       $page   Page number
     * @param User|null $author User entity
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void;
}
