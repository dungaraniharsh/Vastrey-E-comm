<?php

namespace Kreeva\ErpIntegration\Model\Order;

use Psr\Log\LoggerInterface;
use Kreeva\ErpIntegration\Model\Order\SyncInvoicePublisher;

class InvoiceManagement
{
    protected $logger;

    protected $syncInvoicePublisher;

    public function __construct(
        LoggerInterface $logger,
        SyncInvoicePublisher $syncInvoicePublisher
    )
    {
        $this->logger = $logger;
        $this->syncInvoicePublisher = $syncInvoicePublisher;
    }
    /**
     * @inheritdoc
     */
    public function invoice($data)
    {
        $response = ['success' => false];
        try {
            $response = ['success' => true];
            $this->syncInvoicePublisher->execute(json_encode($data));
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        $returnArray = json_encode($response);
        return $returnArray;
   }
}
