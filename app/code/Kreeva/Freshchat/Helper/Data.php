<?php

namespace Kreeva\Freshchat\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Module\Dir;

class Data extends AbstractHelper
{
    protected $assetRepository;
    
    protected $xmlParser;
    
    protected $logger;
    
    protected $moduleDir;

    public function __construct(
        Context $context,
        Repository $assetRepository,
        Parser $xmlParser,
        LoggerInterface $logger,
        Dir $moduleDir
    ) {
        $this->assetRepository = $assetRepository;
        $this->xmlParser = $xmlParser;
        $this->logger = $logger;
        $this->moduleDir = $moduleDir;
        parent::__construct($context);
    }
    
    public function getCountryCode($country) {
        $countryCode = false;
        $countryCodes = $this->getCountryCodeList();
        foreach ($countryCodes['resources']['item'] as $countryData) {
            if ($countryData['iso2'] == $country) {
                $countryCode = $countryData['countryCode'];
                break;
            }
        }
        if ($countryCode)
            return '+'.$countryCode;
        return false;
    }
    
    protected function getCountryCodeList() {
        $countryCodexmlFilePath = $this->getCountryCodeXmlFile();
        $this->xmlParser->load($countryCodexmlFilePath);
        return $this->xmlParser->xmlToArray();
    }
    
    protected function getCountryCodeXmlFile() {
        $moduleViewPath = $this->moduleDir->getDir('Kreeva_Freshchat', Dir::MODULE_VIEW_DIR).'/countrycodes.xml';
        return $moduleViewPath;
    }
}