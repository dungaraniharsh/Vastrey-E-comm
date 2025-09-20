<?php

/**
 * Copyright � 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vastrey\CustomOptions\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;

class CustomOptions extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions {
    /*     * #@+
     * Field values
     * 
     */

    const FIELD_QTY_NAME = 'is_international';
    const FIELD_BUST_SIZE_NAME = 'bust_size';
    const FIELD_DOMESTIC_NAME = 'is_domestic';

    /**
     * Get config for grid for "select" types
     * 
     * @param int $sortOrder
     * @return array
     */
    protected function getSelectTypeGridConfig($sortOrder) {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Value'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(10),
                        static::FIELD_PRICE_NAME => $this->getPriceFieldConfig(20),
                        static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(30, ['fit' => true]),
                        static::FIELD_SKU_NAME => $this->getSkuFieldConfig(40),
                        static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(50),
                        static::FIELD_BUST_SIZE_NAME => $this->getBustSizeFieldConfig(55),
                        static::FIELD_QTY_NAME => $this->getQtyFieldConfig(60),
                        static::FIELD_DOMESTIC_NAME => $this->getisdomesticFieldConfig(65),
                        static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(70)
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for "Inventory" fields
     * 
     * @param $sortOrder 
     * @param array $options
     * @return array
     */
    protected function getQtyFieldConfig($sortOrder, array $options = []) {
        return array_replace_recursive(
                [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Is International?'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => 'is_international',
                        'dataType' => Number::NAME,
                        'additionalClasses' => 'admin__field-small',
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
                ], $options
        );
    }

    protected function getisdomesticFieldConfig($sortOrder, array $options = []) {
        return array_replace_recursive(
                [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Is Domestic?'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => 'is_domestic',
                        'dataType' => Number::NAME,
                        'additionalClasses' => 'admin__field-small',
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
                ], $options
        );
    }

    protected function getBustSizeFieldConfig($sortOrder, array $options = []) {
        return array_replace_recursive(
                [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Bust Size'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_BUST_SIZE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder
                        
                    ],
                ],
            ],
                ], $options
        );
    }

}
