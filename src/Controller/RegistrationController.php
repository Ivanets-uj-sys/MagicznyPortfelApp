<?php

/**
 * Registration Controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\Type\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RegistrationController.
 */
class RegistrationController extends AbstractController
{
    /**
     * Register.
     *
     * @param Request                     $request        HTTP Request
     * @param UserPasswordHasherInterface $passwordHasher PasswordHasher
     * @param EntityManagerInterface      $entityManager  Entity manager
     * @param UserRepository              $userRepository User Repository
     * @param TranslatorInterface         $translator     Translator
     *
     * @return Response HTTP Response
     */
    #[Route('/{_locale}/register', name: 'app_register', requirements: ['_locale' => 'en|pl'], methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        $user = new User();
        $user->setPassword('temporary');
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if ($existingUser) {
                $this->addFlash('warning', $translator->trans('registration.email_already_used', [], 'registration'));

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('registration.success', [], 'registration'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
