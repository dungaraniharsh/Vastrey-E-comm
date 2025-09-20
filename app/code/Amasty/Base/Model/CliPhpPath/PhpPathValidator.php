<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */

declare(strict_types=1);

namespace Amasty\Base\Model\CliPhpPath;

use Amasty\Base\Exceptions\PhpFunctionIsDisabled;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Shell;

class PhpPathValidator
{
    const VERSION_REGEXP = '/PHP [\d\.]+ \(cli\)/';

    /** @var DriverInterface */
    private $fsDriver;

    /** @var Shell */
    private $shell;

    /** @var array */
    private $validationResult = [];

    public function __construct(
        DriverInterface $fsDriver,
        Shell $shell
    ) {
        $this->fsDriver = $fsDriver;
        $this->shell = $shell;
    }

    /**
     * @param string $phpPath
     * @return bool
     * @throws PhpFunctionIsDisabled
     */
    public function isPhpPathValid(string $phpPath = ''): bool
    {
        $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
        if (in_array('exec', $disabled)) {
            throw new PhpFunctionIsDisabled(__('The PHP function exec is disabled.'
                . ' Please contact your system administrator or your hosting provider.'));
        }

        if (empty($phpPath)) {
            return false;
        }

        if (isset($this->validationResult[$phpPath])) {
            return $this->validationResult[$phpPath];
        }

        try {
            $phpPath = $this->fsDriver->getRealPath($phpPath);
            if (!$phpPath || !$this->fsDriver->isFile($phpPath)) {
                return $this->validationResult[$phpPath] = false;
            }
        } catch (\Exception $e) {
            return $this->validationResult[$phpPath] = false;
        }

        try {
            //Check user input to prevent probably exploits
            $version = (string)$this->shell->execute($phpPath . ' %s', ['--version']);
        } catch (\Exception $e) {
            return $this->validationResult[$phpPath] = false;
        }

        return $this->validationResult[$phpPath] = (bool)preg_match(self::VERSION_REGEXP, $version);
    }
}
