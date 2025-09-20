<?php

namespace Kreeva\ErpIntegration\Ui\Component\Listing\Columns\EntityType;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

     /**
      * @return array
      */
     public function toOptionArray()
     {
         $this->options = [];
         $this->options[] = ['value' => 'order', 'label' => __('Order')];
         $this->options[] = ['value' => 'order_update', 'label' => __('Order Update')];
         $this->options[] = ['value' => 'invoice', 'label' => __('Invoice')];
         $this->options[] = ['value' => 'shipment', 'label' => __('Shipment')];
         $this->options[] = ['value' => 'order_cancel', 'label' => __('Order Cancel')];
         return $this->options;
     }
}
