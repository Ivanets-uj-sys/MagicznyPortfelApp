<?php

/**
 * Category Voter.
 */

namespace App\Security\Voter;

use App\Entity\Category;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/** Class Category Voter. */
final class CategoryVoter extends Voter
{
    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'CATEGORY_VIEW';
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'CATEGORY_EDIT';
    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'CATEGORY_DELETE';

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
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Category;
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

        if (!$subject instanceof Category) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($user),
            self::DELETE => $this->canDelete($user),
            self::VIEW => $this->canView($user),
            default => false,
        };
    }

    /**
     * Checks if admin can edit category.
     *
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(UserInterface $user): bool
    {

        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Checks if admin can delete category.
     *
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Checks if admin can view category.
     *
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canView(UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
