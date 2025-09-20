<?php

namespace Kreeva\ErpIntegration\Model\ResourceModel\IntegrationMapper;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'kreeva_erp_intgration_mapper_collection';
    protected $_eventObject = 'erp_intgration_mapper_collection';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init('Kreeva\ErpIntegration\Model\IntegrationMapper', 'Kreeva\ErpIntegration\Model\ResourceModel\IntegrationMapper');
    }

}
