<?php
/*
 * Copyright © 2023 Studio Raz. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SR\Clarity\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Clarity extends Template
{

    const XML_CLARITY_TRACKING_CODE = 'srclarity/general/tracking_code';
    const XML_CLARITY_ENBALED = 'srclarity/general/enabled';

    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param Template\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getClarityTrackingCode(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_CLARITY_TRACKING_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_CLARITY_ENBALED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
