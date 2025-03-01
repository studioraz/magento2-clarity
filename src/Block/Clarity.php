<?php
/*
 * Copyright © 2023 Studio Raz. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SR\Clarity\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Framework\App\Http\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Module\ModuleListInterface;

class Clarity extends Template
{

    const XML_CLARITY_TRACKING_CODE = 'srclarity/general/tracking_code';
    const XML_CLARITY_ENBALED = 'srclarity/general/enabled';
    const XML_CLARITY_SESSION_COOKIE_TRACKER = 'srclarity/general/session_cookie_tracker';
    const SUCCESS_PAGE_URL = 'checkout/onepage/success';
    const HYVA_THEME_MODULE = 'Hyva_Theme';

    protected ScopeConfigInterface $scopeConfig;
    protected CustomerSession $customerSession;
    protected GroupRepository $groupRepository;
    protected CheckoutSession $checkoutSession;
    protected Context $httpContext;
    protected OrderRepositoryInterface $orderRepository;
    protected Http $request;
    protected ModuleListInterface $moduleList;

    /**
     * @param Template\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerSession $customerSession
     * @param GroupRepository $groupRepository
     * @param CheckoutSession $checkoutSession
     * @param Context $httpContext
     * @param OrderRepositoryInterface $orderRepository
     * @param Http $request
     * @param ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        Template\Context     $context,
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        GroupRepository $groupRepository,
        CheckoutSession $checkoutSession,
        Context $httpContext,
        OrderRepositoryInterface $orderRepository,
        Http $request,
        ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->checkoutSession = $checkoutSession;
        $this->httpContext = $httpContext;
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->moduleList = $moduleList;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getClarityTrackingCode(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_CLARITY_TRACKING_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_CLARITY_ENBALED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabledSessionCookieTracker(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_CLARITY_SESSION_COOKIE_TRACKER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getCustomerEmail(): string
    {
        // Check if the customer is logged in
        if ($this->isCustomerLoggedIn()) {
            return $this->customerSession->getCustomer()->getEmail() ?? '';
        }

        // Check if it's an order success page
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        if ($lastOrderId && $this->isCheckoutSuccessPage()) {
            try {
                return $this->getOrder($lastOrderId)->getCustomerEmail();
            } catch (\Exception $e) {
                return '';
            }
        }
        return '';
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        if ($lastOrderId && $this->isCheckoutSuccessPage()) {
            try {
                return $this->getOrder($lastOrderId)->getIncrementId();
            } catch (\Exception $e) {
                return  '';
            }
        }
        return '';
    }

    /**
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->checkoutSession->getQuoteId();
    }

    /**
     * @return int|null
     */
    public function getSessionId()
    {
        return $this->customerSession->getSessionId();
    }

    /**
     * @return bool
     */
    protected function isCustomerLoggedIn(): bool
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return bool
     */
    protected function isCheckoutSuccessPage(): bool
    {
        $currentUrl = $this->request->getUri()->getPath();
        return trim($currentUrl, '/') === self::SUCCESS_PAGE_URL;
    }

    /**
     * @param $lastOrderId
     * @return OrderInterface|null
     * @throws \Exception
     */
    protected function getOrder($lastOrderId): ?OrderInterface
    {
        try {
            return $this->orderRepository->get($lastOrderId);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function isHyvaTheme(): bool
    {
        // Check if the Hyvä module is installed
        return $this->moduleList->has(self::HYVA_THEME_MODULE);
    }
}
