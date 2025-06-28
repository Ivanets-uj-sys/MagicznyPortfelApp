<?php

/**
 * Wallet controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Service\OperationService;
use App\Entity\Wallet;
use App\Form\Type\WalletType;
use App\Service\WalletServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WalletController.
 */
#[Route('/{_locale}/wallet', requirements: ['_locale' => 'en|pl'])]
class WalletController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param WalletServiceInterface $walletService    Wallet service
     * @param TranslatorInterface    $translator       Translator
     * @param OperationService       $operationService Operation service
     */
    public function __construct(private readonly WalletServiceInterface $walletService, private readonly TranslatorInterface $translator, private readonly OperationService $operationService)
    {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'wallet_index',
        methods: 'GET'
    )]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException($this->translator->trans('message.access_denied', [], 'wallet'));
        }
        $pagination = $this->walletService->getPaginatedList($page, $user);

        return $this->render('wallet/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'wallet_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function view(Wallet $wallet): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if ($wallet->getAuthor() !== $user) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found', [], 'wallet')
            );

            return $this->redirectToRoute('wallet_index');
        }
        $operations = $wallet->getOperations();
        $balance = $this->operationService->getWalletBalance($wallet, $user);

        return $this->render(
            'wallet/view.html.twig',
            [
                'wallet' => $wallet,
                'operations' => $operations,
                'balance' => $balance,
            ]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'wallet_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $wallet = new Wallet();
        $wallet->setAuthor($this->getUser());
        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->save($wallet);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully', [], 'wallet')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Wallet  $wallet  Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'wallet_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    public function edit(Request $request, Wallet $wallet): Response
    {
        if ($wallet->getAuthor() !== $this->getUser()) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found', [], 'wallet')
            );

            return $this->redirectToRoute('wallet_index');
        }
        $form = $this->createForm(
            WalletType::class,
            $wallet,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('wallet_edit', ['id' => $wallet->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->save($wallet);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully', [], 'tag')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/edit.html.twig',
            [
                'form' => $form->createView(),
                'wallet' => $wallet,
            ]
        );
    }
}
