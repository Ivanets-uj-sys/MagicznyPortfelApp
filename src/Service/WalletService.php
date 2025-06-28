<?php

/**
 * Wallet service.
 */

namespace App\Service;

use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\User;

/**
 * Class WalletService.
 */
class WalletService implements WalletServiceInterface
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
     * @param WalletRepository   $walletRepository Wallet repository
     * @param PaginatorInterface $paginator        Paginator
     */
    public function __construct(private readonly WalletRepository $walletRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int       $page   Page number
     * @param User|null $author User entity
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->walletRepository->queryAll($author),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['wallet.id', 'wallet.createdAt', 'wallet.updatedAt', 'wallet.title'],
                'defaultSortFieldName' => 'wallet.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void
    {
        $this->walletRepository->save($wallet);
    }
}
