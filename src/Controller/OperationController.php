<?php

/**
 * Operation controller.
 */

namespace App\Controller;

use App\Dto\OperationListInputFiltersDto;
use App\Entity\Operation;
use App\Entity\Wallet;
use App\Entity\User;
use App\Form\Type\OperationType;
use App\Security\Voter\OperationVoter;
use App\Service\OperationServiceInterface;
use App\Resolver\OperationListInputFiltersDtoResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class OperationController.
 *
 * Index, View, Create, CreateWithWallet, Edit
 */
#[Route('/{_locale}/operation', requirements: ['_locale' => 'en|pl'])]
class OperationController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param OperationServiceInterface $operationService Operation service
     * @param TranslatorInterface       $translator       Translator interface
     */
    public function __construct(private readonly OperationServiceInterface $operationService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param OperationListInputFiltersDto $filters Filters for operation list
     * @param int                          $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'operation_index', methods: 'GET')]
    public function index(#[MapQueryString(resolver: OperationListInputFiltersDtoResolver::class)] OperationListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \LogicException($this->translator->trans('message.user_not_auto', [], 'operation'));
        }
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $pagination = $this->operationService->getPaginatedListForAdmin($page, $filters);
            $totalBalance = 0;
        }
        $pagination = $this->operationService->getPaginatedList($page, $user, $filters);
        $totalBalance = $this->operationService->getTotalBalance($user);

        return $this->render('operation/index.html.twig', [
            'pagination' => $pagination,
            'total_balance' => $totalBalance,
        ]);
    }

    /**
     * View action.
     *
     * @param Operation $operation Operation entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'operation_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    #[IsGranted(OperationVoter::VIEW, subject: 'operation')]
    public function view(Operation $operation): Response
    {
        return $this->render(
            'operation/view.html.twig',
            ['operation' => $operation]
        );
    }

    /**
     * Creates a new operation associated with the wallet identified by the given ID.
     *
     * @param Request                $request  Request
     * @param EntityManagerInterface $em       Entity Manager Interface
     * @param int                    $walletId Wallet Id
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create/{walletId}',
        name: 'operation_create_with_wallet',
        methods: 'GET|POST'
    )]
    public function createWithWallet(Request $request, EntityManagerInterface $em, int $walletId): Response
    {
        $wallet = $em->getRepository(Wallet::class)->find($walletId);
        $user = $this->getUser();

        if (!$wallet) {
            $this->addFlash('danger', $this->translator->trans('message.wallet_not_found', [], 'wallet'));
        }
        if ($wallet->getAuthor() !== $user) {
            throw $this->createAccessDeniedException($this->translator->trans('message.author_not_found', [], 'operation'));
        }

        $operation = new Operation();
        $operation->setWallet($wallet);
        $form = $this->createForm(OperationType::class, $operation, [
            'is_create' => true,
            'wallet_locked' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->operationService->canBeCreated($operation, $user)) {
                $this->operationService->initializeBalance($operation, $user);
                $this->operationService->save($operation);

                $this->addFlash('success', $this->translator->trans('message.created_successfully', [], 'operation'));

                return $this->redirectToRoute('operation_index');
            }
            $this->addFlash(
                'danger',
                $this->translator->trans('message.balance_is_too_low', [], 'operation')
            );
        }

        return $this->render('operation/create.html.twig', [
            'form' => $form->createView(),
            'wallet' => $wallet,
            'wallet_locked' => true,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * Creates a new operation with the ability to choose the wallet.
     *
     * @param Request $request Request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'operation_create',
        methods: 'GET|POST'
    )]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $operation = new Operation();
        $form = $this->createForm(OperationType::class, $operation, [
            'user' => $this->getUser(),
            'is_create' => true,
            'wallet_locked' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wallet = $operation->getWallet();
            if (null === $wallet) {
                throw new \LogicException($this->translator->trans('message.wallet_not_set', [], 'wallet'));
            }
            if ($wallet->getAuthor() !== $user) {
                throw $this->createAccessDeniedException($this->translator->trans('message.author_not_found', [], 'operation'));
            }
            if ($this->operationService->createOperation($operation, $user)) {
                $this->addFlash('success', $this->translator->trans('message.created_successfully', [], 'operation'));

                return $this->redirectToRoute('operation_index');
            }
            $this->addFlash('danger', $this->translator->trans('message.balance_is_too_low', [], 'operation'));
        }

        return $this->render(
            'operation/create.html.twig',
            [
                'form' => $form->createView(),
                'wallet_locked' => false,
                'user' => $this->getUser(), ]
        );
    }

    /**
     * Edit action.
     *
     * @param Request   $request   HTTP request
     * @param Operation $operation Operation entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'operation_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    #[IsGranted(OperationVoter::EDIT, subject: 'operation')]
    public function edit(Request $request, Operation $operation): Response
    {
        $form = $this->createForm(
            OperationType::class,
            $operation,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('operation_edit', ['id' => $operation->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->operationService->save($operation);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully', [], 'operation')
            );

            return $this->redirectToRoute('operation_index');
        }

        return $this->render(
            'operation/edit.html.twig',
            [
                'form' => $form->createView(),
                'operation' => $operation,
                'wallet_locked' => false,
                'user' => $this->getUser(),
            ]
        );
    }
}
