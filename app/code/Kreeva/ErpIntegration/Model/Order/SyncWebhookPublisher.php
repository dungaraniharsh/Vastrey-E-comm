<?php

namespace  Kreeva\ErpIntegration\Model\Order;

use Kreeva\ErpIntegration\Model\ErpintegrationFactory;

class SyncWebhookPublisher
{
    const TOPIC_NAME = 'kreeva.orders.webhook.sync';

    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    private $publisher;

    private $erpIntMgtFactory;

    private $jsonDecoder;

    private $orderFactory;

    /**
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     */
    public function __construct(
        \Magento\Framework\MessageQueue\PublisherInterface $publisher,
        ErpintegrationFactory $erpIntMgtFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->publisher = $publisher;
        $this->erpIntMgtFactory = $erpIntMgtFactory;
        $this->jsonDecoder = $jsonDecoder;
        $this->orderFactory = $orderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sync-webhook.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('SyncWebhookPublisher');
        $result = $this->publisher->publish(self::TOPIC_NAME, $data);
        if ($result) {
            $logger->info('result');
            $this->insertMessageQueueMgt($result['message_id'], $result['data']);
        }
    }



    private function insertMessageQueueMgt($messageId, $data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sync-webhook.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('insertMessageQueueMgt');

        $dataArray = $this->jsonDecoder->decode($this->jsonDecoder->decode($data));
        $logger->info(print_r($dataArray, true));
        $intMgtModel = $this->erpIntMgtFactory->create();
        $intMgtModel->setData('entity_type',$dataArray['entity_type']);
        $intMgtModel->setData('entity_id',$dataArray['magento_entity_id']);
        $intMgtModel->setData('sync_data',$data);
        $intMgtModel->setData('message_id',$messageId);
        $intMgtModel->setData('from','ERP');
        $intMgtModel->setData('to','Magento');
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
