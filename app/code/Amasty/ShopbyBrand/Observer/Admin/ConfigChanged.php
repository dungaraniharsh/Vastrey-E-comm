<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


declare(strict_types=1);

namespace Amasty\ShopbyBrand\Observer\Admin;

use Amasty\ShopbyBrand\Model\Brand\OptionsUpdater;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ConfigChanged implements ObserverInterface
{
    /**
     * @var OptionsUpdater
     */
    private $optionsUpdater;

    public function __construct(OptionsUpdater $optionsUpdater)
    {
        $this->optionsUpdater = $optionsUpdater;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->optionsUpdater->execute();
    }
}
