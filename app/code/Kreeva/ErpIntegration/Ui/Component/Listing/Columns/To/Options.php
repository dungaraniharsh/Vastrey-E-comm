<?php

namespace Kreeva\ErpIntegration\Ui\Component\Listing\Columns\To;

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
         $this->options[] = ['value' => 'Magento', 'label' => __('Magento')];
         $this->options[] = ['value' => 'ERP', 'label' => __('ERP')];
         return $this->options;
     }
}
