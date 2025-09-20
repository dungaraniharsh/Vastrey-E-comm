<?php

namespace Kreeva\Multistoreview\Block;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;

class StoreCookie extends \Magento\Framework\View\Element\Template
{
    protected $storeManager;
    
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getStoreData()
    {
        $params = [
            'storeID' => $this->storeManager->getStore()->getStoreId(),
            'storeCode' => $this->storeManager->getStore()->getCode()
        ];
        return json_encode($params);
    }
}
