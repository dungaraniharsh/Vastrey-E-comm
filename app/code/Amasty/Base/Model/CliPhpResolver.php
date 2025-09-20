<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */

declare(strict_types=1);

namespace Amasty\Base\Model;

use Amasty\Base\Exceptions\PhpFunctionIsDisabled;
use Amasty\Base\Model\CliPhpPath\PhpPathValidator;
use Symfony\Component\Process\PhpExecutableFinder;

class CliPhpResolver
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PhpExecutableFinder
     */
    private $phpExecutableFinder;

    /**
     * @var PhpPathValidator
     */
    private $phpPathValidator;

    public function __construct(
        Config $config,
        PhpExecutableFinder $phpExecutableFinder,
        PhpPathValidator $phpPathValidator
    ) {
        $this->config = $config;
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->phpPathValidator = $phpPathValidator;
    }

    /**
     * @return string
     * @throws PhpFunctionIsDisabled
     */
    public function getPath(): string
    {
        $phpPath = $this->config->getCliPhpPath();
        if (!$this->phpPathValidator->isPhpPathValid($phpPath)) {
            $phpPath = $this->phpExecutableFinder->find() ?: 'php';
        }

        return  $phpPath;
    }
}
