<?php

namespace Kreeva\ErpIntegration\Model\ResourceModel\Erpintegration;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'kreeva_erp_integration_collection';
    protected $_eventObject = 'erp_integration_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kreeva\ErpIntegration\Model\Erpintegration', 'Kreeva\ErpIntegration\Model\ResourceModel\Erpintegration');
    }
}
