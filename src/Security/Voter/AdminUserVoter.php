<?php

/**
 * Admin User Voter.
 */

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/** Class AdminUserVoter */
final class AdminUserVoter extends Voter
{
    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'USER_VIEW';
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'USER_EDIT';
    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'USER_DELETE';

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
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof User;
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

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if admin can edit user.
     *
     * @param User $targetUser  Target user
     * @param User $currentUser Current user
     *
     * @return bool Result
     */
    private function canEdit(User $targetUser, UserInterface $currentUser): bool
    {
        return $currentUser !== $targetUser && in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    /**
     * Checks if admin can delete user.
     *
     * @param User $targetUser  Target user
     * @param User $currentUser Current user
     *
     * @return bool Result
     */
    private function canDelete(User $targetUser, UserInterface $currentUser): bool
    {
        return $currentUser !== $targetUser && in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    /**
     * Checks if admin can view user.
     *
     * @param User $targetUser  Target user
     * @param User $currentUser Current user
     *
     * @return bool Result
     */
    private function canView(User $targetUser, UserInterface $currentUser): bool
    {
        return $currentUser !== $targetUser && in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }
}
