<?php

/**
 * Admin User Controller.
 */

namespace App\Controller;

use App\Repository\WalletRepository;
use App\Entity\User;
use App\Form\Type\AdminUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\AdminUserServiceInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Class AdminUserController.
 */
#[Route('/{_locale}/admin/user', requirements: ['_locale' => 'en|pl'], methods: ['GET', 'POST'])]
class AdminUserController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param AdminUserServiceInterface $adminuserService AdminUser Service
     * @param TranslatorInterface       $translator       Translator
     */
    public function __construct(private readonly AdminUserServiceInterface $adminuserService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route('/', name: 'admin_user_index', methods: 'GET')]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->adminuserService->getPaginatedList($page);
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * View action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP Response
     */
    #[Route('/{id}', name: 'admin_user_view', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(AdminUserVoter::USER_VIEW, subject: 'user')]
    public function view(User $user): Response
    {
        return $this->render('admin/user/view.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Edit action.
     *
     * @param User                        $user           User entity
     * @param Request                     $request        HTTP Request
     * @param UserRepository              $userRepository User repository
     * @param EntityManagerInterface      $entityManager  Entity manager
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param TranslatorInterface         $translator     Translator
     *
     * @return Response HTTP Response
     */
    #[Route(
        '/{id}/edit',
        name: 'admin_user_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    #[IsGranted(AdminUserVoter::USER_EDIT, subject: 'user')]
    public function edit(User $user, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(AdminUserType::class, $user, ['is_edit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $newPassword = $form->get('newPassword')->getData();

            $user->setEmail($email);

            if ($newPassword) {
                $hashed = $passwordHasher->hashPassword($user, $newPassword);
                // upgradePassword najczęściej tylko ustawia hasło i ewentualnie persistuje, ale nie flushuje
                $userRepository->upgradePassword($user, $hashed);
            }

            // Zapisz wszystkie zmiany (np. email i jeśli brak flushu w upgradePassword)
            $userRepository->save($user);

            $this->addFlash('success', $translator->trans('change_user.success', [], 'login'));

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Create action.
     *
     * @param Request                     $request        HTTP Request
     * @param EntityManagerInterface      $entityManager  Entity manager
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    #[IsGranted(AdminUserVoter::CREATE, subject: 'user')]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $user->setPassword('temporary');
        $form = $this->createForm(AdminUserType::class, $user, [
            'is_edit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('newPassword')->get('first')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Użytkownik został utworzony.');

                return $this->redirectToRoute('admin_user_index');
            } catch (UniqueConstraintViolationException $e) {
                $form->get('email')->addError(new \Symfony\Component\Form\FormError(
                    $this->translator->trans('change_user.error.email_used', [], 'admin')
                ));
            }
        }

        return $this->render('admin/user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete action.
     *
     * @param Request                $request          HTTP request
     * @param User                   $user             User entity
     * @param EntityManagerInterface $entityManager    Entity manager
     * @param WalletRepository       $walletRepository Wallet repository
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'admin_user_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(AdminUserVoter::USER_DELETE, subject: 'user')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, WalletRepository $walletRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $wallets = $walletRepository->findBy(['author' => $user]);

        foreach ($wallets as $wallet) {
            $entityManager->remove($wallet);
        }

        $entityManager->remove($user);
        $entityManager->flush();
        if ($this->isCsrfTokenValid('delete-user-'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.deleted_successfully', [], 'login'));
        }

        return $this->redirectToRoute('admin_user_index');
    }
}
