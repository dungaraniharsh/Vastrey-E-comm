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

class SyncInvoice
{
    /**
    * @var \Zend\Log\Logger
    */
    private $logger;
    /**
    * @var string
    */
    private $logFileName = 'order-sync-consumer.log';
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

    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        IntegrationMapperFactory $integrationMapperFactory,
        MapperCollection $integrationMapperCollectionFactory,
        CollectionFactory $integrationMgtCollectionFactory,
        ApiClient $apiClient,
        OrderRepositoryFactory $orderRepositoryFactory,
        MessageEncoder $messageEncoder,
        ErpintegrationFactory $erpintegrationFactory
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
    }
    public function process(EnvelopeInterface $message)
    {
        try {
          $properties = $message->getProperties();
          $topicName = $properties['topic_name'];
          $messageId = $properties['message_id'];
          $orderData = $this->messageEncoder->decode($topicName, $message->getBody());
          $this->logger->info(print_r($orderData,true));
          $orderData = json_decode($orderData, true);
          $order = $this->orderRepositoryFactory->create()->get($orderData['entity_id']);
          $this->logger->info('Message ID -----------> ' . $messageId);
          $this->logger->info($order->getId() . ' -----> ' . $order->getIncrementId());
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

              $mapperCollection = $this->integrationMapperCollectionFactory->create()
                                                ->addFieldToFilter('entity_type', 'invoice')
                                                ->addFieldToFilter('entity_id', $orderData['entity_id'])
                                                ->getFirstItem();

              if ($mapperCollection->getData('id')) {
                  if ($order->getStatus() == 'canceled') {
                      $url = OrderUrls::STATUS_CHANGE_URL;
                      $url = str_replace("{{orderId}}", $mapperCollection->getData('erp_id'), $url);
                      $this->logger->info('cancel url -----> ' . $url);
                      $requestData = $this->buildCancelRequestData($mapperCollection);
                      $this->logger->info(print_r($requestData,true));
                      $this->logger->info(json_encode($requestData));
                      $response = $this->apiClient->sendPutRequest($url, $requestData);
                      if (isset($response['status']) && $response['status'] == 'ok') {
                          $syncStatus = 'complete';
                      } else {
                          $syncStatus = 'error';
                      }
                  } else {
                      $syncStatus = 'already-sync';
                  }
              } else {
                  $requestData = $this->buildRequestData($order);
                  $this->logger->info(print_r($requestData, true));
                  $this->logger->info(print_r(json_encode($requestData), true));

                  $response = $this->apiClient->sendPostReqest(OrderUrls::CREATE_URL, $requestData);
                  $this->logger->info(print_r($response, true));
                  if (isset($response['status']) && $response['status'] == 'ok') {
                      $mapper = $this->integrationMapperFactory->create();
                      $mapper->setData('entity_type','order');
                      $mapper->setData('entity_id', $orderData['entity_id']);
                      $mapper->setData('erp_id', $response['data']['id']);
                      $mapper->save();
                      $syncStatus = 'complete';
                  } else {
                      $syncStatus = 'error';
                  }
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
        }
    }

    public function buildCancelRequestData($mapperCollection)
    {
      $data = [];
      $data = ["status" => 10,
                "reason" => "change of mind"];
      return $data;
    }
    public function buildRequestData($order)
    {
        $billingAddress = $order->getBillingAddress();
        $bStreet = $billingAddress->getData('street');
        $billing_address = [
        "name"=> $billingAddress->getData('address_type'), // Fixed
        "addressLine1"=> (is_array($bStreet) && isset($bStreet[0])) ? $bStreet:$bStreet,
        "addressLine2"=> (is_array($bStreet) && isset($bStreet[1])) ? $bStreet[1]:'',
        "city"=> $billingAddress->getData('city'),
        "country"=> $billingAddress->getData('country_id'),
        "pincode"=> $billingAddress->getData('postcode'),
        "state"=> $billingAddress->getData('region'),
        "state_code"=> $billingAddress->getData('region_id') //GST State code
        ];

        $shippingAddress = $order->getShippingAddress();
        $shStreet = $shippingAddress->getData('street');
        $shipping_address = [
        "name"=> $shippingAddress->getData('address_type'), // Fixed
        "addressLine1"=> (is_array($shStreet) && isset($shStreet[0])) ? $shStreet[0]:$shStreet,
        "addressLine2"=> (is_array($shStreet) && isset($shStreet[1])) ? $shStreet[1]:'',
        "city"=> $shippingAddress->getData('city'),
        "country"=> $shippingAddress->getData('country_id'),
        "pincode"=> $shippingAddress->getData('postcode'),
        "state"=> $shippingAddress->getData('region'),
        "state_code"=> $shippingAddress->getData('region_id') //GST State code
        ];
        $items = [];
        foreach ($order->getItems() as $item) {
        $items[] = ["product"=> [
          "name"=> $item->getData('name'),
          "sku"=> "7302",//$item->getData('sku'),
          "category"=> "LEHENGA CHOLI",
        ],
        "qty"=> $item->getData('qty_ordered'),
        "rate"=> $item->getData('price'),
        "amount"=> $item->getData('row_total')
        ];
        }
        $createdAt = $order->getCreatedAt();
        $data = [];
        $data  = [
        "date" => date('Y-M-d', strtotime($createdAt)),
        "number" => $order->getIncrementId(),
        "payment_mode" => 1,
        "channel"=> "6148385c367a07c490343323",
        "contact"=> [
            "first_name"=> $order->getCustomerFirstname(),
            "last_name"=> $order->getCustomerLastname(),
            "email"=> $order->getCustomerEmail(),
            "company_name"=> $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
            "addresses"=> [
                $billing_address,
                $shipping_address
            ],
            "mobile_numbers"=> [
                [
                    "code"=> "91",
                    "number"=> $billingAddress->getData('telephone')
                ],
                [
                    "code"=> "91",
                    "number"=> $shippingAddress->getData('telephone')
                ]
            ]
        ],
        "reference_id"=> $order->getIncrementId(), //Kreeva Order Id
        //"courier"=> $order->getShippingMethod(), // Default Courrier name, We identify from name, Let us know possible values
        "priority"=> 1, // 0=Regular, 1 = Urgent
        "items"=> $items,
        "total_amount"=> $order->getBaseGrandTotal(), // Total Amount,
        "notes"=> "", // User notes if any
        "status"=> 1, // 1=Pending/Open
        ];
        return $data;
    }
}
