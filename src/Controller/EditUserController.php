<?php

/**
 * Edit User Controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\EditUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;

/**
 * Class EditUserController.
 */
class EditUserController extends AbstractController
{
    /**
     * Edit User.
     *
     * @param Request                $request        HTTP Request
     * @param User                   $user           User entity
     * @param EntityManagerInterface $entityManager  Entity Manager
     * @param TranslatorInterface    $translator     Translator
     * @param UserRepository         $userRepository User Repository
     *
     * @return Response HTTP Response
     */
    #[Route('/{_locale}/user/{id}/edit', name: 'user_edit', requirements: ['_locale' => 'en|pl'])]
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')
            && $currentUser !== $user
        ) {
            throw $this->createAccessDeniedException($translator->trans('change_password.error.no_access', [], 'login'));
        }

        $form = $this->createForm(EditUserType::class, null, [
            'email' => $user->getEmail(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $existing = $userRepository->findOneBy(['email' => $email]);
            if ($existing && $existing !== $user) {
                $form->get('email')->addError(new FormError(
                    $translator->trans('edit_user.error.email_used', [], 'login')
                ));
            } else {
                $user->setEmail($email);
                $entityManager->flush();

                $this->addFlash('success', $translator->trans('edit_user.success', [], 'login'));

                return $this->redirectToRoute('operation_index');
            }
        }

        return $this->render('security/edit_user.html.twig', [
            'editUserForm' => $form->createView(),
        ]);
    }
}
