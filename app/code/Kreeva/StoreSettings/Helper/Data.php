<?php

namespace Kreeva\StoreSettings\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function isDefaultStoreShippingCountry($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            'storesettings/general/shipping_country_default_store',
            $scope
        );
    }
}
