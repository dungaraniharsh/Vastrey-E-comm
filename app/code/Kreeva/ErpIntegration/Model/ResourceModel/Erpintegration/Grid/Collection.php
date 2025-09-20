<?php

namespace Kreeva\ErpIntegration\Model\ResourceModel\Erpintegration\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init('Kreeva\ErpIntegration\Model\Erpintegration', ' Kreeva\ErpIntegration\Model\ResourceModel\Erpintegration');
        parent::_construct();
    }

}
