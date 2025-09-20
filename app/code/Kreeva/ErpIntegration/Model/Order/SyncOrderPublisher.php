<?php

namespace  Kreeva\ErpIntegration\Model\Order;

use Kreeva\ErpIntegration\Model\ErpintegrationFactory;

class SyncOrderPublisher
{
    const TOPIC_NAME = 'kreeva.orders.sync';

    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    private $publisher;

    private $erpIntMgtFactory;

    private $jsonDecoder;

    /**
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     */
    public function __construct(
        \Magento\Framework\MessageQueue\PublisherInterface $publisher,
        ErpintegrationFactory $erpIntMgtFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    )
    {
        $this->publisher = $publisher;
        $this->erpIntMgtFactory = $erpIntMgtFactory;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($order)
    {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sync-order.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$logger->info('SyncOrderPublisher');
        $result = $this->publisher->publish(self::TOPIC_NAME, $order);
        if ($result) {
            $logger->info('result');
            $this->insertMessageQueueMgt($result['message_id'], $result['data']);
        }
    }



    private function insertMessageQueueMgt($messageId, $data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sync-order.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('insertMessageQueueMgt');

        $dataArray = $this->jsonDecoder->decode($this->jsonDecoder->decode($data));
        $logger->info(print_r($dataArray,true));
        $intMgtModel = $this->erpIntMgtFactory->create();
        $intMgtModel->setData('entity_type','order');
        $intMgtModel->setData('entity_id',$dataArray['entity_id']);
        $intMgtModel->setData('sync_data',$data);
        $intMgtModel->setData('message_id',$messageId);
        $intMgtModel->setData('from','Magento');
        $intMgtModel->setData('to','ERP');
        $intMgtModel->setData('status','pending');
        try {

            $d = $intMgtModel->save();
            $logger->info('after Save');
            $logger->info(print_r($d->getData(),true));
        } catch (\Exception $e) {
            $logger->info($e->getMessage());
        }


        return true;
    }
}
