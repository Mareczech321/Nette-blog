<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Model\UserFacade;
use App\Model\PremiumFacade;
use App\Model\OrderFacade;
use App\Model\Session\CartSession;
use App\Model\Mapper\OrderMapper;
use App\Model\Facade\PDFFacade;

final class PremiumPresenter extends BasePresenter
{
    public function __construct(
        private UserFacade $userFacade,
        private PremiumFacade $premiumFacade,
        private OrderFacade $orderFacade,
        private CartSession $cartSession,
        private OrderMapper $orderMapper,
        private PdfFacade $pdfFacade
    ) {}

    public function renderCart(): void
    {
        $selectedPlanCode = $this->cartSession->getPlan();
        $allPlans = $this->premiumFacade->getAllPlans();

        $this->template->plans = $allPlans;

        $this->template->selectedPlan = ($selectedPlanCode && isset($allPlans[$selectedPlanCode]))
            ? $allPlans[$selectedPlanCode]
            : null;

        $this->template->hasItem = $selectedPlanCode !== null;
    }

    public function handleAddToCart(string $planId): void
    {
        if (isset($this->premiumFacade->getAllPlans()[$planId])) {
            $this->cartSession->setPlan($planId);
            $this->flashMessage('Vybrali jste tarif.', 'success');
        }
        $this->redirect('cart');
    }

    public function handleCheckout(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Pro dokončení objednávky se musíte přihlásit.', 'error');
            $this->redirect('Sign:in');
        }

        $userID = (int) $this->getUser()->getId();

        $planId = $this->cartSession->getPlan();
        $allPlans = $this->premiumFacade->getAllPlans();

        if ($planId && isset($allPlans[$planId])) {
            $plan = $allPlans[$planId];

            $this->userFacade->updatePremium($userID, $plan->duration);

            $orderDTO = $this->orderMapper->createFromPlan($plan, $userID);
            $orderDTO->createdAt = new \DateTimeImmutable();
            $this->orderFacade->createOrder($orderDTO);

            $this->cartSession->setSuccess(true);
            $this->cartSession->removePlan();

            $this->redirect('success');
        } else {
            $this->flashMessage('Košík je prázdný.', 'error');
            $this->redirect('cart');
        }
    }

    public function renderSuccess(): void
    {
        if (!$this->cartSession->isSuccess()) {
            $this->redirect('Home:default');
        }

        $userId = (int) $this->getUser()->getId();

        $order = $this->orderFacade->getLatestOrderByUser($userId);

        if ($order === null) {
            $this->cartSession->removeSuccess();
            $this->redirect('Home:default');
        }

        $this->template->order = $order;

        $this->cartSession->removeSuccess();
    }

    public function renderHistory(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Pro zobrazení historie se musíte přihlásit.', 'warning');
            $this->redirect('Sign:in');
        }

        $userId = (int) $this->getUser()->getId();
        $identity = $this->getUser()->getIdentity();

        $this->template->orders = $this->orderFacade->getAllOrdersByUser($userId);

        $stats = $this->orderFacade->getUserStats($userId);
        $this->template->totalSpent = $stats['totalSpent'];
        $this->template->totalMonths = $stats['totalMonths'];

        $this->template->premiumUntil = $identity->premium_duration ?? null;
    }

    public function actionInvoice(int $orderId): void
    {
        // 1. Získáme data o objednávce pomocí existujícího repozitáře/fasády
        $order = $this->orderFacade->getOrderById($orderId);

        if (!$order) {
            $this->error('Objednávka nebyla nalezena.');
        }

        // 2. Připravíme šablonu pro fakturu/objednávku
        $template = $this->createTemplate();
        $template->setFile(__DIR__ . '/../templates/Premium/invoice.latte');
        $template->order = $order; // Předáváme OrderDTO do šablony

        // 1. Řekneme PHP: "Nic neposílej, všechno si schovávej do krabice"
        ob_start();

        // 2. Vykreslíme šablonu.
        // Všechny echo výstupy z šablony se teď "ztratí" v té krabici.
        $template->render();

        // 3. Řekneme PHP: "Dej mi obsah krabice a vymaž ji"
        $html = ob_get_clean();
        // 3. Vygenerujeme PDF přes naši službu
        $filename = 'objednavka-' . $order->order_number . '.pdf';
        $response = $this->pdfFacade->createPdfResponse((string) $html, $filename);

        $this->sendResponse($response);
    }
}