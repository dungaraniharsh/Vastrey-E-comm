<?php

namespace Kreeva\ErpIntegration\Helper;

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

    public function getGstCode($stateId) {
        $gstCode = false;
        $gstCodes = $this->getGstCodesList();
        foreach ($gstCodes['resources']['item'] as $state) {
            if ($state['magento_state_id'] == $stateId) {
                $gstCode = $state['gst_code'];
                break;
            }
        }
        return $gstCode;
    }

    protected function getGstCodesList() {
        $gstCodexmlFilePath = $this->getGstCodesXmlFile();
        $this->xmlParser->load($gstCodexmlFilePath);
        return $this->xmlParser->xmlToArray();
    }

    protected function getGstCodesXmlFile() {
        $moduleViewPath = $this->moduleDir->getDir('Kreeva_ErpIntegration', Dir::MODULE_VIEW_DIR).'/gst_codes.xml';
        return $moduleViewPath;
    }
}
