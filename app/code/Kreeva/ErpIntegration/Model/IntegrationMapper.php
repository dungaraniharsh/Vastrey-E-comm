<?php


namespace Kreeva\ErpIntegration\Model;



class IntegrationMapper extends \Magento\Framework\Model\AbstractModel
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'kr_integration_mapper';

    /**
     * @var string
     */
    protected $_cacheTag = 'kr_integration_mapper';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'kr_integration_mapper';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Kreeva\ErpIntegration\Model\ResourceModel\IntegrationMapper');
    }
}
