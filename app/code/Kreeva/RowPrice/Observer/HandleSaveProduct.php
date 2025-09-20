<?php

namespace Kreeva\RowPrice\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product\Action as ProductAction;

class HandleSaveProduct implements ObserverInterface
{
	protected $request;
	protected $storeManager;
	protected $productRepository;
	protected $productAction;
	protected $messageManager;
	
	/**
     * HandleSaveProduct constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
		\Magento\Framework\App\RequestInterface $request,
		StoreManagerInterface $storeManager,
		ProductRepository $productRepository,
		ProductAction $productAction,
		\Magento\Framework\Message\ManagerInterface $messageManager
	)
    {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->productAction = $productAction;
        $this->messageManager = $messageManager;
    }
    
    public function execute(Observer $observer)
    {
		try {
			$params = $this->request->getParams();
			
			if ($this->storeManager->getStore()->getCode() != 'row') {
				if (isset($params['id'])) {
					$storeId = $this->getRowStoreId();
					$rowProduct = $this->productRepository->getById($params['id'], false, $storeId);
					$attributesToUpdate = [];
					if (isset($params['product']['rowprice']) && $params['product']['rowprice'] != $rowProduct->getData('price')) {
						$attributesToUpdate['price'] = $params['product']['rowprice'];
					}
					if (isset($params['product']['rowspecialprice']) && $params['product']['rowspecialprice'] != $rowProduct->getData('special_price')) {
						$attributesToUpdate['special_price'] = $params['product']['rowspecialprice'];
					}
					if (count($attributesToUpdate) > 0 ) {
						$this->productAction->updateAttributes([$params['id']], $attributesToUpdate, $storeId);
					}
				}
			}
		} catch (\Exception $e) {
			$this->messageManager->addError(__($e->getMessage()));
		}
    }
    
    public function getRowStoreId()
    {
		$storecode = 'row';
		$store = $this->storeManager->getStore('row');
		return $store->getId();
	}
}
