<?php


namespace Kreeva\ErpIntegration\Model;



class Erpintegration extends \Magento\Framework\Model\AbstractModel
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'kr_integration_management';

    /**
     * @var string
     */
    protected $_cacheTag = 'kr_integration_management';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'kr_integration_management';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Kreeva\ErpIntegration\Model\ResourceModel\Erpintegration');
    }
}


