<?php

/**
 * Operation voter.
 */

namespace App\Security\Voter;

use App\Entity\Operation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/** Class Operation Voter. */
final class OperationVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'OPERATION_EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'OPERATION_VIEW';

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
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Operation;
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
        if (!$subject instanceof Operation) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can edit operation.
     *
     * @param Operation     $operation Operation entity
     * @param UserInterface $user      User
     *
     * @return bool Result
     */
    private function canEdit(Operation $operation, UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $wallet = $operation->getWallet();
        if (null === $wallet) {
            return false;
        }

        return $wallet->getAuthor() === $user;
    }

    /**
     * Checks if user can view operation.
     *
     * @param Operation     $operation Operation entity
     * @param UserInterface $user      User
     *
     * @return bool Result
     */
    private function canView(Operation $operation, UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }
        $wallet = $operation->getWallet();
        if (null === $wallet) {
            return false;
        }

        return $operation->getWallet()->getAuthor() === $user;
    }
}
