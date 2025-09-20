<?php

namespace Kreeva\ErpIntegration\Ui\Component\Listing\Columns\Status;

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
         $this->options[] = ['value' => 'pending', 'label' => __('Pending')];
         $this->options[] = ['value' => 'complete', 'label' => __('Complete')];
         $this->options[] = ['value' => 'error', 'label' => __('Failed')];
         $this->options[] = ['value' => 'exception', 'label' => __('Exception')];
         $this->options[] = ['value' => 'Resync', 'label' => __('Resync')];
         $this->options[] = ['value' => 'already-sync', 'label' => __('Already Sync')];
         return $this->options;
     }
}
