<?php

namespace Kreeva\ErpIntegration\Model\Order;

use Psr\Log\LoggerInterface;
use Kreeva\ErpIntegration\Model\Order\SyncWebhookPublisher;

class WebhookManagement
{
    protected $logger;

    protected $syncWebhookPublisher;

    protected $request;

    protected $orderFactory;

    public function __construct(
        LoggerInterface $logger,
        SyncWebhookPublisher $syncWebhookPublisher,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->logger = $logger;
        $this->syncWebhookPublisher = $syncWebhookPublisher;
        $this->request = $request;
        $this->orderFactory = $orderFactory;
    }
    /**
     * @inheritdoc
     */
    public function webhook($payload)
    {
        $response = ['success' => false];
        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/web-api.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Your text message');
            foreach ($payload as $key => $data) {
                $orderPayload = $data->getPayload();
                $logger->info(print_r($orderPayload,true));
                $order = $this->orderFactory->create()->loadByIncrementId($orderPayload['reference_id']);
                $orderPayload['magento_entity_id'] = $order->getEntityId();
                switch ($orderPayload['status']) {
                  case 4:
                        $orderPayload['entity_type'] = 'invoice';
                      break;
                  case 7:
                        $orderPayload['entity_type'] = 'shipment';
                      break;
                  case 10:
                        $orderPayload['entity_type'] = 'order_cancel';
                      break;
                  default:
                        $orderPayload['entity_type'] = 'order_update';
                      break;
                }
                $this->syncWebhookPublisher->execute(json_encode($orderPayload));
            }

            $response = ['success' => true];

        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        $returnArray = json_encode($response);
        return $returnArray;
   }
}
