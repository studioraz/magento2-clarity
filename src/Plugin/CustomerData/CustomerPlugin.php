<?php
/*
 * Copyright © 2023 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Clarity\Plugin\CustomerData;

use Magento\Customer\CustomerData\Customer;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerPlugin
{

    protected CurrentCustomer $currentCustomer;
    protected CustomerSession $customerSession;
    protected GroupRepository $groupRepository;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param CustomerSession $customerSession
     * @param GroupRepository $groupRepository
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        CustomerSession $customerSession,
        GroupRepository $groupRepository
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
    }

    /**
     * AFTER:Plugin
     * @see \Magento\Customer\CustomerData\Customer::getSectionData()
     *
     * @param Customer $subject
     * @param $result
     * @return mixed
     */
    public function afterGetSectionData(Customer $subject, $result)
    {
        if (count($result)) {
            try {
                $customer = $this->currentCustomer->getCustomer();
                $result['email'] = $customer->getEmail();
                $result['groupName'] = $this->getCustomerGroup();
            } catch (\Exception $e) {
                return $result;
            }
        }
        return $result;
    }

    protected function getCustomerGroup(): string
    {
        //Need to add this validation as the customer group id for not logged in returns as 1 (General)
        $customerGroupId =$this->customerSession->getCustomer()->getGroupId();
        try {
            return $this->groupRepository->getById($customerGroupId)->getCode();
        } catch (\Exception | NoSuchEntityException $e) {
            return '';
        }
    }
}
