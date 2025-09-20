<?php

namespace Kreeva\ErpIntegration\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const INTEGRATION_API_URL = 'erpintegration/general/apiurl';

    const INTEGRATION_API_KEY = 'erpintegration/general/apikey';

    const INTEGRATION_COMPANY_ID = 'erpintegration/general/companyid';

    const INTEGRATION_CHANNEL_ID = 'erpintegration/general/channelid';

    const INTEGRATION_RAZORPAY_ID = 'erpintegration/general/razorpayid';

    const INTEGRATION_INTERNAL_TOKEN = 'erpintegration/general/internaltoken';

    const INTEGRATION_INTL_SHIPPING_METHOD = 'erpintegration/general/intlshippingmethod';
    
    const INTEGRATION_PAYU_ID = 'erpintegration/general/payuid';

    protected $scopeConfig;

    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->scopeConfig->getValue(self::INTEGRATION_API_URL, ScopeInterface::SCOPE_STORE);
    }

    public function getApiKey()
    {
        return $this->scopeConfig->getValue(self::INTEGRATION_API_KEY, ScopeInterface::SCOPE_STORE);
        //return $this->apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjYxNWU4ZjhiZmJlZjUzMTFmYWJhYTMxNSIsIm5hbWUiOiJLcmVldmEiLCJlbWFpbCI6ImtyZWV2YUBhcnlhZGVzaWducy5jby5pbiIsImlhdCI6MTYzMzU4NzA4NCwiZXhwIjoxNjM4NzcxMDg0fQ.xP4lOGK_aS6Uoal0AYSfcLMfzeimsGOPXsmSv1zbvBc';
    }

    public function getCompanyId()
    {
        return $this->scopeConfig->getValue(self::INTEGRATION_COMPANY_ID, ScopeInterface::SCOPE_STORE);
    }

    public function getChannelId()
    {
        return $this->scopeConfig->getValue(self::INTEGRATION_CHANNEL_ID, ScopeInterface::SCOPE_STORE);
    }

    public function getRazorPayId()
    {
        return $this->scopeConfig->getValue(self::INTEGRATION_RAZORPAY_ID, ScopeInterface::SCOPE_STORE);
    }

    public function getInternalToken()
    {
        return $this->scopeConfig->getValue(self::INTEGRATION_INTERNAL_TOKEN, ScopeInterface::SCOPE_STORE);
    }

    public function getIntlShippingMethod()
    {
        return $this->scopeConfig->getValue(self::INTEGRATION_INTL_SHIPPING_METHOD, ScopeInterface::SCOPE_STORE);
    }
    
    public function getPayUId()
    {
        return $this->scopeConfig->getValue(self::INTEGRATION_PAYU_ID, ScopeInterface::SCOPE_STORE);
    }
}
