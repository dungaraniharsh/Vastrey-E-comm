<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Kreeva\ErpIntegration\Block\Adminhtml\Order\View;

use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Address;
use Kreeva\ErpIntegration\Model\ResourceModel\IntegrationMapper\CollectionFactory;

class OrderSync extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    protected $integrationMapperCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        CollectionFactory $integrationMapperCollectionFactory,
        array $data = []
    ) {
        $this->integrationMapperCollectionFactory = $integrationMapperCollectionFactory;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function getSyncStatus()
    {
        $order = $this->getOrder();
        if ($order->getId()) {
            $mapper = $this->integrationMapperCollectionFactory->create()
                    ->addFieldToFilter('entity_id',$order->getId())
                    ->addFieldToFilter('entity_type', 'order')
                    ->getFirstItem();
            if ($mapper->getId()) {
                return 'yes';
            }
        }
        return 'no';
    }
}
