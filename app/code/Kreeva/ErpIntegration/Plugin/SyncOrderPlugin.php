<?php

namespace Kreeva\ErpIntegration\Plugin;

use Kreeva\ErpIntegration\Model\Order\SyncOrderPublisher;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Customer\Model\AccountManagement;

class SyncOrderPlugin
{
    private $syncOrderPublisher;

    protected $storeManager;

    protected $customer;

    protected $accountManagement;

    public function __construct(
      SyncOrderPublisher $syncOrderPublisher,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Customer\Model\CustomerFactory $customer,
      \Magento\Customer\Api\AccountManagementInterface $accountManagement
    ) {
        $this->syncOrderPublisher = $syncOrderPublisher;
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->accountManagement = $accountManagement;
    }

    public function afterSave(\Magento\Sales\Model\OrderRepository $subject, $order)
    {
        $this->syncOrderPublisher->execute(json_encode($order->getData()));
        return $order;
    }

    public function beforeSave(\Magento\Sales\Model\OrderRepository $subject, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        if ($order->getCustomerIsGuest() == 1) {
            $shippingAddress = $order->getShippingAddress();
            $customer= $this->customer->create();
            $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
            $customer->loadByEmail($shippingAddress->getEmail());
            if (!$customer->getId()) {
                $customer->setEmail($shippingAddress->getEmail());
                $customer->setFirstname($shippingAddress->getFirstname());
                $customer->setLastname($shippingAddress->getLastname());
                $customer->setForceConfirmed(true);
                $customer->save();
                $customerId = $customer->getId();
                $this->accountManagement->initiatePasswordReset($shippingAddress->getEmail(), AccountManagement::EMAIL_RESET, $customer->getWebsiteId());
            }
            $order->setCustomerId($customer->getId());
            $order->setCustomerIsGuest(0);
        }
    }
}
