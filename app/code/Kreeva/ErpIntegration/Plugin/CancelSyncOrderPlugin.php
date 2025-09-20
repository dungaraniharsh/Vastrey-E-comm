<?php

namespace Kreeva\ErpIntegration\Plugin;

use Kreeva\ErpIntegration\Model\Order\SyncOrderPublisher;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Sales\Api\OrderRepositoryInterface;

class CancelSyncOrderPlugin
{
    private $syncOrderPublisher;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct(
      SyncOrderPublisher $syncOrderPublisher,
      OrderRepositoryInterface $orderRepository
    ) {
        $this->syncOrderPublisher = $syncOrderPublisher;
        $this->orderRepository = $orderRepository;
    }

    public function afterCancel(\Magento\Sales\Model\Service\OrderService $subject, $result)
    {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sync-order.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$logger->info('Your text message');
$logger->info(get_class($order));
$logger->info($order->getId());
        $this->syncOrderPublisher->execute($order);
        return $order;
    }
}
