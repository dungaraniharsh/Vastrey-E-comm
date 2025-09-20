<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */

declare(strict_types=1);

namespace Amasty\Base\Model\Config\Backend;

use Amasty\Base\Model\CliPhpPath\PhpPathValidator;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class CliPhpPath extends Value
{
    /**
     * @var PhpPathValidator
     */
    private $phpPathValidator;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        PhpPathValidator $phpPathValidator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->phpPathValidator = $phpPathValidator;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $value = trim((string)$this->getValue());
        if (!empty($value)) {
            if (!$this->phpPathValidator->isPhpPathValid($value)) {
                throw new LocalizedException(__('Invalid CLI PHP path: "%1".', $value));
            }
        }

        $this->setValue($value);

        return parent::beforeSave();
    }
}
