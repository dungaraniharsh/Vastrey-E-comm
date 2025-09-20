<?php

namespace Kreeva\ErpIntegration\Model\Order;

use Psr\Log\LoggerInterface;
use Kreeva\ErpIntegration\Model\Order\SyncShippingPublisher;

class ShippingManagement
{
    protected $logger;

    protected $syncInvoicePublisher;

    public function __construct(
        LoggerInterface $logger,
        SyncShippingPublisher $syncShippingPublisher
    )
    {
        $this->logger = $logger;
        $this->syncShippingPublisher = $syncShippingPublisher;
    }
    /**
     * @inheritdoc
     */
    public function ship($data)
    {
        $response = ['success' => false];
        try {
            $response = ['success' => true];
            $this->syncShippingPublisher->execute(json_encode($data));
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        $returnArray = json_encode($response);
        return $returnArray;
   }
}
