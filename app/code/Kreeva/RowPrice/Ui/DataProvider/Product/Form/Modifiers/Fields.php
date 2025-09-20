<?php

namespace Kreeva\RowPrice\Ui\DataProvider\Product\Form\Modifiers;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Store\Model\StoreManagerInterface;

class Fields extends AbstractModifier
{
    private $locator;
    protected $storeManager;
    protected $productRepository;
    
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }
    
    public function modifyData(array $data)
    {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
		$action     = $request->getActionName();
		if ($action != 'new') {		 
			$product = $this->locator->getProduct();
			if ($product) {
				$storeId = $this->getRowStoreId();
				$rowProduct = $this->productRepository->getById($product->getId(), false, $storeId);
				$rowPrice = $rowProduct->getData('price');
				$rowSpecialPrice = $rowProduct->getData('special_price');
				$data = array_replace_recursive(
					 $data,
					 [
						 $product->getId() => [
							 'product' => [
								 'rowprice' => $rowPrice,
								 'rowspecialprice' => $rowSpecialPrice
							 ]
						 ]
					 ]
				);
			}
		}
        return $data;
    }
    
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                'product-details' => [
					'children' => [
						'custom_field' => $this->getRowPriceField()
						],
                ],
                'advanced-pricing' => [
					'children' => [
						'row_special_price_field' => $this->getRowSpecialPriceField()
					]
                ]
            ]
        );
        return $meta;
    }
    
    public function getRowPriceField()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Row Price'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => 'rowprice',
                        'dataType' => Price::NAME,
                        'sortOrder' => 41,
                        'addbefore' => '$'
                    ],
                ],
            ],
        ];
    }
    
    public function getRowSpecialPriceField()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Row Special Price'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => 'rowspecialprice',
                        'dataType' => Price::NAME,
                        'sortOrder' => 1,
                        'addbefore' => '$'
                    ],
                ],
            ],
        ];
    }
    
    public function getRowStoreId()
    {
		$storecode = 'row';
		$store = $this->storeManager->getStore('row');
		return $store->getId();
	}
}
