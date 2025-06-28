<?php

/**
 * Change Password Controller.
 */

namespace App\Controller;

use App\Form\Type\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ChangePasswordController.
 */
class ChangePasswordController extends AbstractController
{
    /**
     * Change Password.
     *
     * @param Request                     $request        HTTP request
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param EntityManagerInterface      $entityManager  Doctrine entity manager
     * @param TranslatorInterface         $translator     Translator
     *
     * @return Response HTTP response
     */
    #[Route('/{_locale}/user/change-password', name: 'user_change_password', requirements: ['_locale' => 'en|pl'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();

            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('change_password.success', [], 'login'));

            return $this->redirectToRoute('operation_index');
        }

        return $this->render('security/change_password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }
}
