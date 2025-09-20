<?php

namespace  Kreeva\ErpIntegration\Model\Order;

use Kreeva\ErpIntegration\Model\IntegrationMapperFactory;
use Kreeva\ErpIntegration\Model\ResourceModel\IntegrationMapper\CollectionFactory as MapperCollection;
use Kreeva\ErpIntegration\Model\ResourceModel\Erpintegration\CollectionFactory;
use Kreeva\ErpIntegration\Model\Source\OrderUrls;
use Kreeva\ErpIntegration\Model\ApiClient;
use Magento\Sales\Model\OrderRepositoryFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Kreeva\ErpIntegration\Model\ErpintegrationFactory;

class SyncWebhook
{
    /**
    * @var \Zend\Log\Logger
    */
    private $logger;
    /**
    * @var string
    */
    private $logFileName = 'order-update-sync-consumer.log';
    /**
    * @var \Magento\Framework\App\Filesystem\DirectoryList
    */
    private $directoryList;

    private $integrationMapperFactory;

    private $integrationMapperCollectionFactory;

    private $integrationMgtCollectionFactory;

    private $apiClient;

    private $orderRepositoryFactory;

    private $messageEncoder;

    private $erpintegrationFactory;

    private $invoiceService;

    private $transactionFactory;

    private $invoiceSender;

    private $_objectManager;

    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        IntegrationMapperFactory $integrationMapperFactory,
        MapperCollection $integrationMapperCollectionFactory,
        CollectionFactory $integrationMgtCollectionFactory,
        ApiClient $apiClient,
        OrderRepositoryFactory $orderRepositoryFactory,
        MessageEncoder $messageEncoder,
        ErpintegrationFactory $erpintegrationFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->directoryList = $directoryList;
        $logDir = $directoryList->getPath('log');
        $writer = new \Zend\Log\Writer\Stream($logDir . DIRECTORY_SEPARATOR . $this->logFileName);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $this->logger = $logger;
        $this->integrationMapperFactory = $integrationMapperFactory;
        $this->integrationMapperCollectionFactory = $integrationMapperCollectionFactory;
        $this->integrationMgtCollectionFactory = $integrationMgtCollectionFactory;
        $this->apiClient = $apiClient;
        $this->orderRepositoryFactory = $orderRepositoryFactory;
        $this->messageEncoder = $messageEncoder;
        $this->erpintegrationFactory = $erpintegrationFactory;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceSender = $invoiceSender;
        $this->_objectManager = $objectManager;
    }
    public function process(EnvelopeInterface $message)
    {
        try {
          $properties = $message->getProperties();
          $topicName = $properties['topic_name'];
          $messageId = $properties['message_id'];
          $orderData = $this->messageEncoder->decode($topicName, $message->getBody());
          $this->logger->info(print_r($orderData,true));

          $this->logger->info('Message ID -----------> ' . $messageId);
          if ($messageId) {
              $this->logger->info('Inner -----------> ' . $messageId);
              $syncStatus = 'processing';
              $response = [];
              $integrationMgt = $this->integrationMgtCollectionFactory->create()
                                                ->addFieldToFilter('message_id',$messageId)
                                                ->getFirstItem();
              $this->logger->info('Management -----> ' . $integrationMgt->getId());
              if ($integrationMgt->getId()) {
                  $erpintegrationModel = $this->erpintegrationFactory->create()->load($integrationMgt->getId());
                  $erpintegrationModel->setData('status', $syncStatus);
                  $erpintegrationModel->save();
              }

              $orderUpdateData = json_decode($orderData, true);
              $order = $this->orderRepositoryFactory->create()->get($orderUpdateData['magento_entity_id']);

              //order update from ERP to Magento
              switch ($orderUpdateData['status']) {
                case 4:
                    $this->generateInvoice($order);
                    $syncStatus = 'complete';
                    $response = ['Invoice Created Successfully'];
                    break;
                case 7:
                    $this->generateShipment($order);
                    $syncStatus = 'complete';
                    $response = ['Shippment Created Successfully'];
                    break;
                case 10:
                    $this->generateCancel($order);
                    $syncStatus = 'complete';
                    $response = ['Order Cancelled Successfully'];
                    break;
                default:
                    $orderUpdated = $this->updateOrderStatus($order, $orderUpdateData['status']);
                    $syncStatus = 'complete';
                    $response = ['Order status Updated Successfully: '.$orderUpdated->getStatus()];
                    break;

              }

              if ($integrationMgt) {
                $erpintegrationModel = $this->erpintegrationFactory->create()->load($integrationMgt->getId());
                $erpintegrationModel->setData('status', $syncStatus);
                $erpintegrationModel->setData('response', json_encode($response));
                $erpintegrationModel->save();
              }
          }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            if ($integrationMgt) {
              $erpintegrationModel = $this->erpintegrationFactory->create()->load($integrationMgt->getId());
              $erpintegrationModel->setData('status', 'exception');
              $erpintegrationModel->setData('response', json_encode($e->getMessage()));
              $erpintegrationModel->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->info($e->getMessage());
            if ($integrationMgt) {
              $erpintegrationModel = $this->erpintegrationFactory->create()->load($integrationMgt->getId());
              $erpintegrationModel->setData('status', 'exception');
              $erpintegrationModel->setData('response', json_encode($e->getMessage()));
              $erpintegrationModel->save();
            }
        }
    }

    public function generateInvoice($order)
    {
        try {
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }
            if(!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                        __('The order does not allow an invoice to be created.')
                    );
            }

            $invoice = $this->invoiceService->prepareInvoice($order);
            if (!$invoice) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t save the invoice right now.'));
            }
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $order->addStatusHistoryComment('Invoice in ERP', false);
            $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();

            // send invoice emails, If you want to stop mail disable below try/catch code
            /*try {
                $this->invoiceSender->send($invoice);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t send the invoice email right now.'));
            }*/
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        return $invoice;
    }

    public function generateShipment($order)
    {
        // Check if order has already shipping or can be shipped
        if (!$order->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You cant create the Shipment.'));
        }

        // Initializzing Object for the order shipment
        $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
        $shipment = $convertOrder->toShipment($order);

        // Looping the Order Items
        foreach ($order->getAllItems() as $orderItem) {

            // Check if the order item has Quantity to ship or is virtual
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }
            $qtyShipped = $orderItem->getQtyToShip();

            // Create Shipment Item with Quantity
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

            // Add Shipment Item to Shipment
            $shipment->addItem($shipmentItem);
        }

        // Register Shipment
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        try {

            // Save created Shipment and Order
            $shipment->save();
            $shipment->getOrder()->save();

            // Send Email
            /*$this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                ->notify($shipment);*/
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    public function generateCancel($order)
    {
        $orderManagement = $this->_objectManager->create('\Magento\Sales\Api\OrderManagementInterface');
        if (!$order->canCancel()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You cant Cancel the Order.'));
        }
        try {
            $orderManagement->cancel($order->getEntityId());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
        return true;
    }

    public function updateOrderStatus($order, $erpOrderStatus)
    {
        switch ($erpOrderStatus) {
            case 2:
                $order->setStatus('mark_as_confirmed');
                break;
            case 5:
                $order->setStatus('ready_to_ship');
                break;
            case 6:
                $order->setStatus('picked_up');
                break;
            case 8:
                $order->setStatus('delivered');
                break;
            case 11:
                $order->setStatus('rto_acknowledge');
                break;
        }
        try {
            $order->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
        return $order;
    }
}
