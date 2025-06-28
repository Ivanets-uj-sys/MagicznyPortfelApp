<?php

/**
 * Wallet Voter.
 */

namespace App\Security\Voter;

use App\Entity\Wallet;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class Wallet Voter.
 */
final class WalletVoter extends Voter
{
    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'WALLET_DELETE';
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'WALLET_EDIT';
    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'WALLET_VIEW';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT, self::VIEW])
            && $subject instanceof Wallet;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        if (!$subject instanceof Wallet) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can delete wallet.
     *
     * @param Wallet        $wallet Wallet entity
     * @param UserInterface $user   User entity
     *
     * @return bool Result
     */
    private function canDelete(Wallet $wallet, UserInterface $user): bool
    {
        return $wallet->getAuthor() === $user;
    }

    /**
     * Checks if user can edit wallet.
     *
     * @param Wallet        $wallet Wallet entity
     * @param UserInterface $user   User entity
     *
     * @return bool Result
     */
    private function canEdit(Wallet $wallet, UserInterface $user): bool
    {
        return $wallet->getAuthor() === $user;
    }

    /**
     * Checks if user can view wallet.
     *
     * @param Wallet        $wallet Wallet entity
     * @param UserInterface $user   User entity
     *
     * @return bool Result
     */
    private function canView(Wallet $wallet, UserInterface $user): bool
    {
        return $wallet->getAuthor() === $user;
    }
}
