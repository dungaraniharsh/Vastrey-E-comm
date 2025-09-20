<?php

namespace Kreeva\ErpIntegration\Plugin\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Kreeva\ErpIntegration\Model\ResourceModel\IntegrationMapper\CollectionFactory;

class OrderSyncButton
{
    protected $integrationMapperCollectionFactory;

    public function __construct(
        CollectionFactory $integrationMapperCollectionFactory
    ) {
        $this->integrationMapperCollectionFactory = $integrationMapperCollectionFactory;
    }

    public function beforeSetLayout(OrderView $subject)
    {
        $mapper = $this->integrationMapperCollectionFactory->create()
                ->addFieldToFilter('entity_id',$subject->getOrderId())
                ->addFieldToFilter('entity_type', 'order')
                ->getFirstItem();
        if (!$mapper->getId()) {
            $url = $subject->getUrl('erpintegration/erpintegration/sync');
            $subject->addButton(
                'order_sync_button',
                [
                    'label' => __('Sync Order'),
                    'class' => __('order-sync-button'),
                    'id' => 'order-view-sync-button',
                    'onclick' => "setLocation('".$url."')"
                ]
            );
        }
    }
}
