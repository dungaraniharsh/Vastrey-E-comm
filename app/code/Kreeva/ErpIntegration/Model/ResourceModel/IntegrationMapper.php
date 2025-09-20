<?php

namespace Kreeva\ErpIntegration\Model\ResourceModel;

class IntegrationMapper extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('kr_integration_mapper', 'id');
    }

}
