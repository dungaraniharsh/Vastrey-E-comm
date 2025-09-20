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
use Kreeva\ErpIntegration\Model\Config;

class SyncOrder
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

    private $erpConfig;

    protected $helper;

    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        IntegrationMapperFactory $integrationMapperFactory,
        MapperCollection $integrationMapperCollectionFactory,
        CollectionFactory $integrationMgtCollectionFactory,
        ApiClient $apiClient,
        OrderRepositoryFactory $orderRepositoryFactory,
        MessageEncoder $messageEncoder,
        ErpintegrationFactory $erpintegrationFactory,
        Config $erpConfig,
        \Kreeva\ErpIntegration\Helper\Data $helper
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
        $this->erpConfig = $erpConfig;
        $this->helper = $helper;
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
                                                ->addFieldToFilter('entity_type', 'order')
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

        $biGstCode = $billingAddress->getData('region_id');
        if ($billingAddress->getData('country_id') == 'IN') {
            $biGstCode = $this->helper->getGstCode($billingAddress->getData('region_id'));
        }
        $billing_address = [
            "name"=> $billingAddress->getData('address_type'), // Fixed
            "addressLine1"=> (is_array($bStreet) && isset($bStreet[0])) ? $bStreet:$bStreet,
            "addressLine2"=> (is_array($bStreet) && isset($bStreet[1])) ? $bStreet[1]:'',
            "city"=> $billingAddress->getData('city'),
            "country"=> $billingAddress->getData('country_id'),
            "pincode"=> $billingAddress->getData('postcode'),
            "state"=> $billingAddress->getData('region'),
            "state_code"=> $biGstCode //GST State code
        ];

        $shippingAddress = $order->getShippingAddress();
        $shStreet = $shippingAddress->getData('street');
        $shGstCode = $shippingAddress->getData('region_id');
        if ($shippingAddress->getData('country_id') == 'IN') {
            $shGstCode = $this->helper->getGstCode($shippingAddress->getData('region_id'));
        }
        $shipping_address = [
            "name"=> $shippingAddress->getData('address_type'), // Fixed
            "addressLine1"=> (is_array($shStreet) && isset($shStreet[0])) ? $shStreet[0]:$shStreet,
            "addressLine2"=> (is_array($shStreet) && isset($shStreet[1])) ? $shStreet[1]:'',
            "city"=> $shippingAddress->getData('city'),
            "country"=> $shippingAddress->getData('country_id'),
            "pincode"=> $shippingAddress->getData('postcode'),
            "state"=> $shippingAddress->getData('region'),
            "state_code"=> $shGstCode //GST State code
        ];
        $items = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customOption = false;
        foreach ($order->getItems() as $item) {
            $categoryName = "LEHENGA CHOLI";
            $product = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface')->getById($item->getData('product_id'));
            $productOptions = $item->getProductOptions();
            /*if ($productOptions && !$customOption) {
                foreach ($productOptions as $option) {
                    if (isset($option[0])) {
                        if (isset($option[0]['value'])) {
                            $value = strtolower($option[0]['value']);
                            if (strpos($value, 'ready to wear') !== false || strpos($value, 'customization') !==  false) {
                                $this->logger->info(print_r('inner',true));
                                $customOption =  true;
                                break;
                            }
                        }
                    }
                }
            }*/
            $categoryCollection = $product->getCategoryCollection();
            foreach ($categoryCollection as $category) {
                if ($category->getLevel() == 2) {

                    $catModel = $objectManager->create('Magento\Catalog\Api\CategoryRepositoryInterface')->get($category->getId());
                    $categoryName = $catModel->getName();
                    break;
                }
            }
            $rowTotal = $item->getData('row_total');
            if ($item->getData('discount_amount')) {
                $rowTotal = $rowTotal - $item->getData('discount_amount');
            }
            $items[] = ["product"=> [
              "name"=> $item->getData('name'),
              "sku"=> $item->getData('sku'),
              "category"=> $categoryName,
            ],
            "qty"=> $item->getData('qty_ordered'),
            "rate"=> $item->getData('price'),
            "amount"=> $rowTotal
            ];
        }
        $createdAt = $order->getCreatedAt();
        $payment = $order->getPayment();
        $this->logger->info(print_r($billingAddress->getData('country_id'), true));
        $this->logger->info(print_r($shippingAddress->getData('country_id'), true));
        $billingPhno = $billingAddress->getData('telephone');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $freshchatHelper = $objectManager->create('\Kreeva\Freshchat\Helper\Data');
        $countryCode = $freshchatHelper->getCountryCode($shippingAddress->getData('country_id'));
        if ($countryCode == false) {
            $countryCode = "91";
        }
        $countryCode = str_replace('+','',$countryCode);
        if (strlen($billingPhno) > 10) {
            $billingPhno = str_replace('+','',$billingPhno);
            $billingPhno = str_replace($countryCode,'',$billingPhno);
            $billingPhno = substr($billingPhno,strlen($billingPhno) - 10);
        }
        $billingPhno = str_replace('+','',$billingPhno);
        $shippingPhno = $shippingAddress->getData('telephone');
        if (strlen($shippingPhno) > 10) {
            $shippingPhno = str_replace('+','',$shippingPhno);
            $shippingPhno = str_replace($countryCode,'',$shippingPhno);
            $shippingPhno = substr($shippingPhno,strlen($shippingPhno) - 10);
        }
        $shippingPhno = str_replace('+','',$shippingPhno);
        $data = [];
        $data  = [
            "date" => date('Y-M-d', strtotime($createdAt)),
            "number" => $order->getIncrementId(),
            "channel"=> $this->erpConfig->getChannelId(),
            "contact"=> [
                "first_name"=> $shippingAddress->getData('firstname'),
                "last_name"=> $shippingAddress->getData('lastname'),
                "email"=> $shippingAddress->getData('email'),
                "company_name"=> $shippingAddress->getData('firstname').' '.$shippingAddress->getData('lastname'),
                "addresses"=> [
                    $billing_address,
                    $shipping_address
                ],
                "mobile_numbers"=> [
                    [
                        "code"=> $countryCode,
                        "number"=> $billingPhno
                    ],
                    [
                        "code"=> $countryCode,
                        "number"=> $shippingPhno
                    ]
                ]
            ],
            "reference_id"=> $order->getIncrementId(), //Kreeva Order Id
            //"courier"=> $order->getShippingMethod(), // Default Courrier name, We identify from name, Let us know possible values
            "priority"=> "0", // 0=Regular, 1 = Urgent
            "items"=> $items,
            "total_amount"=> $order->getGrandTotal(), // Total Amount,
            "notes"=> "", // User notes if any
            "status"=> 1, // 1=Pending/Open
            "shipping_method"=> $order->getShippingMethod(),
            "shipping_charge" => $order->getShippingAmount()
        ];
        if ($payment->getData('method') == 'payu')
        {
            $data['payment_mode']  = 2;
            $data['payment_gateway']  = $this->erpConfig->getPayUId();
            $data['payment_reference'] = $payment->getData('last_trans_id');
        } else {
            $data['payment_mode']  = 1;
        }
        if ($customOption) {
            $data['notes'] = 'Please review the order in Ecommerce to get stiching options or ready to wear';
        }
        return $data;
    }
}
