<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */

declare(strict_types=1);

namespace Amasty\Base\Exceptions;

class PhpFunctionIsDisabled extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(\Magento\Framework\Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if (!$phrase) {
            $phrase = __('PHP function is disabled.');
        }
        parent::__construct($phrase, $cause, (int) $code);
    }
}
