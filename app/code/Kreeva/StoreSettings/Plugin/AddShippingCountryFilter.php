<?php

namespace Kreeva\StoreSettings\Plugin;

class AddShippingCountryFilter
{
	protected $helper;

    public function __construct(
      \Kreeva\StoreSettings\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    public function afterLoadByStore(\Magento\Directory\Model\ResourceModel\Country\Collection $subject, $result)
    {
        if ($this->helper->isDefaultStoreShippingCountry()) {
            $result->addFieldToFilter("country_id", ['in' => ['IN']]);
        }

        return $result;
    }
}
