<?php

namespace Kreeva\StoreSettings\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as AuthContext;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Credit\Helper\Data;
use GeoIp2\Database\Reader;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Module\Dir;

class Checkstore extends Action
{
    private $customerSession;
    private $authContext;
    private $storeManager;
    protected $remote;
    protected $moduleDir;

    public function __construct(
        Context $context,
        Session $session,
        AuthContext $authContext,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        RemoteAddress $remote,
        Dir $moduleDir
    ) {
        $this->customerSession = $session;
        $this->authContext = $authContext;
        $this->storeManager = $storeManager;
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->remote = $remote;
        $this->moduleDir = $moduleDir;
    }
    public function execute()
    {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/store_settings.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
        $data = [];
        $ip = $this->remote->getRemoteAddress();
        $logger->info(print_r($ip,true));
        $reader = new Reader($this->getCountrymmdbFile());
        $record = $reader->country($ip);
        $logger->info(print_r($record,true));
        $resultJson = $this->resultJsonFactory->create();
        $data['country_code'] = $countryCode = $record->country->isoCode;
        $data['store_code'] = $storeCode = $this->storeManager->getStore()->getCode();
        $data['redirect'] = 0;
        if ($countryCode != 'IN' && $storeCode == 'default') {
			$currentUrl = $this->_request->getParam('current_url');
			$data['redirect'] = 1;
			$data['redirect_url'] = $this->_url->getUrl('stores/store/redirect', 
						["___store" => "row", "___from_store" => "default",
						"redirect_url" => $currentUrl,
						"countrycode" => $countryCode]);
		}
        return $resultJson->setData($data);
    }
    
    protected function getCountrymmdbFile() {
        $moduleViewPath = $this->moduleDir->getDir('Kreeva_StoreSettings', Dir::MODULE_VIEW_DIR).'/GeoLite2-Country.mmdb';
        return $moduleViewPath;
    }
}
